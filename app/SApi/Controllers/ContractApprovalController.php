<?php

namespace App\SApi\Controllers;

use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Durable exchange only: OA owns approval decisions; CRM owns the sales contract. */
class ContractApprovalController extends Controller
{
    private const TABLE = 'platform_contract_approvals';

    private function application(Request $request): PlatformApplication
    {
        return $request->attributes->get('sapi_application');
    }

    private function enabled(PlatformApplication $app, PlatformEnterprise $enterprise): void
    {
        abort_unless($app->is_enabled && ! $app->expires_at?->isPast() && $enterprise->is_enabled
            && DB::table('platform_application_grants')->where('application_id', $app->id)
                ->where('enterprise_id', $enterprise->id)->where('employee_id', 0)->where('is_enabled', true)->exists(), 403, '企业应用未开通');
    }

    private function visible(Request $request, string $enterpriseUuid): Builder
    {
        $app = $this->application($request);
        $access = app(IntegrationAccess::class);
        $enterprise = $access->enterprise($enterpriseUuid);
        $this->enabled($app, $enterprise);
        abort_unless($access->allows($app, 'contracts.submit', $enterprise->id)
            || $access->allows($app, 'contracts.receive', $enterprise->id), 403, '应用未获合同交换授权');

        return DB::table(self::TABLE)->where('enterprise_id', $enterprise->id)
            ->where(fn ($query) => $query->where('source_application_id', $app->id)->orWhere('target_application_id', $app->id));
    }

    public function store(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate([
            'uuid' => 'required|uuid', 'enterprise_uuid' => 'required|uuid', 'contract_uuid' => 'required|uuid',
            'cancel_requested' => 'sometimes|boolean',
            'revision' => 'required|integer|min:1', 'target_application_code' => 'required|string|max:50',
            'applicant_user_uuid' => 'required|uuid', 'applicant_employment_uuid' => 'required|uuid',
            'snapshot_json' => 'required|string|max:200000|json', 'snapshot_hash' => 'required|string|size:64|regex:/^[a-f0-9]+$/',
        ]);
        abort_unless(hash_equals(hash('sha256', $data['snapshot_json']), $data['snapshot_hash']), 422, '合同快照摘要不匹配');
        $snapshot = json_decode($data['snapshot_json'], true, 32, JSON_THROW_ON_ERROR);
        validator($snapshot, [
            'number' => 'required|string|max:191', 'name' => 'required|string|max:191',
            'customer_name' => 'required|string|max:191', 'amount' => 'required|numeric|min:0|max:999999999999.99',
            'currency' => 'required|string|size:3', 'terms' => 'nullable|string|max:100000',
            'starts_at' => 'nullable|date_format:Y-m-d', 'ends_at' => 'nullable|date_format:Y-m-d|after_or_equal:starts_at',
        ])->validate();
        $source = $this->application($request);
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($source, 'contracts.submit', $enterprise->id);
        $target = PlatformApplication::query()->where('code', $data['target_application_code'])->firstOrFail();
        abort_if($source->id === $target->id, 422, '审批来源和接收应用不能相同');
        $access->require($target, 'contracts.receive', $enterprise->id);
        $this->enabled($source, $enterprise);
        $this->enabled($target, $enterprise);
        $identity = ['user_uuid' => $data['applicant_user_uuid'], 'enterprise_uuid' => $enterprise->uuid,
            'employment_uuid' => $data['applicant_employment_uuid'], 'application_code' => $source->code];
        // Replays may reconcile an earlier accepted submission after the applicant leaves.
        $existing = DB::table(self::TABLE)->where('uuid', $data['uuid'])->first();
        if (! $existing) {
            $sourceActive = $access->check($source, $identity)['active'];
            abort_unless(($data['cancel_requested'] ?? false) || $sourceActive, 403, '申请人授权已失效');
            abort_unless(($data['cancel_requested'] ?? false) || $access->check($target, [...$identity, 'application_code' => $target->code])['active'], 403, '申请人未获OA授权');
        }
        $row = DB::transaction(function () use ($data, $enterprise, $source, $target) {
            DB::table(self::TABLE)->insertOrIgnore([
                'uuid' => $data['uuid'], 'enterprise_id' => $enterprise->id, 'enterprise_uuid' => $enterprise->uuid, 'source_application_id' => $source->id,
                'target_application_id' => $target->id, 'contract_uuid' => $data['contract_uuid'], 'revision' => $data['revision'],
                'applicant_user_uuid' => $data['applicant_user_uuid'], 'applicant_employment_uuid' => $data['applicant_employment_uuid'],
                'snapshot_json' => $data['snapshot_json'], 'snapshot_hash' => $data['snapshot_hash'],
                'status' => ($data['cancel_requested'] ?? false) ? 'cancelling' : 'queued', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $row = DB::table(self::TABLE)->where('uuid', $data['uuid'])->lockForUpdate()->first();
            abort_unless($row && $row->enterprise_id === $enterprise->id && $row->source_application_id === $source->id
                && $row->target_application_id === $target->id && $row->contract_uuid === $data['contract_uuid']
                && $row->revision === (int) $data['revision'] && hash_equals($row->snapshot_hash, $data['snapshot_hash'])
                && $row->applicant_user_uuid === $data['applicant_user_uuid']
                && $row->applicant_employment_uuid === $data['applicant_employment_uuid'], 409, '同一审批请求不能更换合同或快照');

            if (($data['cancel_requested'] ?? false) && in_array($row->status, ['queued', 'pending'], true)) {
                DB::table(self::TABLE)->where('id', $row->id)->update(['status' => 'cancelling', 'updated_at' => now()]);
                $row = DB::table(self::TABLE)->where('id', $row->id)->first();
            }

            return $row;
        });

        return $this->success(['request' => $row]);
    }

    public function index(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'after_id' => 'nullable|integer|min:0']);
        $app = $this->application($request);
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'contracts.receive', $enterprise->id);
        $rows = $this->visible($request, $data['enterprise_uuid'])->where('target_application_id', $app->id)
            ->whereIn('status', ['queued', 'cancelling'])
            ->where('id', '>', $data['after_id'] ?? 0)->orderBy('id')->limit(100)->get();

        return $this->success(['items' => $rows, 'next_id' => $rows->last()?->id, 'has_more' => $rows->count() === 100]);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid']);

        return $this->success(['request' => $this->visible($request, $data['enterprise_uuid'])->where('uuid', $uuid)->firstOrFail()]);
    }

    public function result(Request $request, string $uuid, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'snapshot_hash' => 'required|string|size:64',
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])],
            'oa_contract_uuid' => 'nullable|uuid|required_unless:status,cancelled', 'oa_instance_uuid' => 'nullable|uuid|required_unless:status,cancelled', 'comment' => 'nullable|string|max:2000']);
        $app = $this->application($request);
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'contracts.receive', $enterprise->id);
        $row = DB::transaction(function () use ($request, $uuid, $data, $app) {
            $row = $this->visible($request, $data['enterprise_uuid'])->where('uuid', $uuid)->where('target_application_id', $app->id)->lockForUpdate()->firstOrFail();
            abort_unless(hash_equals($row->snapshot_hash, $data['snapshot_hash']), 409, '审批版本不匹配');
            abort_if(($row->oa_contract_uuid && $row->oa_contract_uuid !== ($data['oa_contract_uuid'] ?? null))
                || ($row->oa_instance_uuid && $row->oa_instance_uuid !== ($data['oa_instance_uuid'] ?? null)), 409, '审批关联不可替换');
            if (in_array($row->status, ['approved', 'rejected', 'cancelled'], true)) {
                abort_unless($row->status === $data['status'], 409, '已结束的审批不能覆盖');

                return $row;
            }
            abort_if($data['status'] === 'cancelled' && $row->status !== 'cancelling', 409, '仅可确认来源已请求撤回的审批');
            abort_if($row->status === 'cancelling' && $data['status'] === 'pending', 409, '请先处理撤回请求');
            DB::table(self::TABLE)->where('id', $row->id)->update([
                'status' => $data['status'], 'oa_contract_uuid' => ($data['oa_contract_uuid'] ?? null),
                'oa_instance_uuid' => ($data['oa_instance_uuid'] ?? null), 'comment' => $data['comment'] ?? null, 'updated_at' => now(),
            ]);

            return DB::table(self::TABLE)->where('id', $row->id)->first();
        });

        return $this->success(['request' => $row]);
    }

    public function cancel(Request $request, string $uuid, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid']);
        $app = $this->application($request);
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'contracts.submit', $enterprise->id);
        $row = DB::transaction(function () use ($request, $data, $uuid, $app) {
            $row = $this->visible($request, $data['enterprise_uuid'])->where('uuid', $uuid)->where('source_application_id', $app->id)->lockForUpdate()->firstOrFail();
            if (in_array($row->status, ['approved', 'rejected', 'cancelled'], true)) {
                return $row;
            }
            DB::table(self::TABLE)->where('id', $row->id)->update(['status' => 'cancelling', 'updated_at' => now()]);

            return DB::table(self::TABLE)->where('id', $row->id)->first();
        });

        return $this->success(['request' => $row]);
    }
}
