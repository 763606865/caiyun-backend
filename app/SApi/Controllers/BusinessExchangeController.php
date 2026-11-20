<?php

namespace App\SApi\Controllers;

use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Durable, enterprise-scoped envelopes. Each business system retains its own write authority. */
class BusinessExchangeController extends Controller
{
    public const KINDS = ['sales_order', 'warehouse_delivery', 'delivery_result', 'order_status', 'cancel_sales_order', 'cancel_delivery', 'production_order', 'production_result', 'warehouse_receipt', 'receipt_result'];

    /**
     * @return array{PlatformApplication,PlatformEnterprise}
     */
    public function authorizeApp(Request $request, string $enterpriseUuid, string $scope): array
    {
        $app = $request->attributes->get('sapi_application');
        $access = app(IntegrationAccess::class);
        $enterprise = $access->enterprise($enterpriseUuid);
        abort_unless($app instanceof PlatformApplication && $app->is_enabled && ! $app->expires_at?->isPast() && $enterprise->is_enabled, 403, '应用或企业不可用');
        $access->require($app, $scope, $enterprise->id);
        abort_unless(DB::table('platform_application_grants')->where('application_id', $app->id)->where('enterprise_id', $enterprise->id)->where('employee_id', 0)->where('is_enabled', true)->exists(), 403, '企业未开通应用');

        return [$app, $enterprise];
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['uuid' => 'required|uuid', 'enterprise_uuid' => 'required|uuid', 'target_application_code' => 'required|string|max:50', 'kind' => ['required', Rule::in(self::KINDS)], 'correlation_uuid' => 'required|uuid', 'payload_json' => 'required|string|json|max:500000', 'payload_hash' => 'required|string|size:64']);
        [$source,$enterprise] = $this->authorizeApp($r, $d['enterprise_uuid'], 'business.send.'.$d['kind']);
        abort_unless(hash_equals(hash('sha256', $d['payload_json']), $d['payload_hash']), 422, '单据快照摘要不匹配');
        if ($d['kind'] === 'sales_order') {
            $body = json_decode($d['payload_json'], true, 32, JSON_THROW_ON_ERROR);
            $approval = DB::table('platform_contract_approvals')->where('uuid', $body['approval_uuid'] ?? '')->first();
            abort_unless($approval !== null && $approval->enterprise_id === $enterprise->id && $approval->source_application_id === $source->id && $approval->contract_uuid === $d['correlation_uuid'] && $approval->status === 'approved' && $approval->revision === ($body['contract_revision'] ?? null) && hash_equals($approval->snapshot_hash, (string) ($body['snapshot_hash'] ?? '')) && hash_equals($approval->snapshot_hash, hash('sha256', (string) ($body['snapshot_json'] ?? ''))), 409, '履约下推必须引用本企业已批准的原始合同快照');
        }
        $target = PlatformApplication::query()->where('code', $d['target_application_code'])->where('is_enabled', true)->firstOrFail();
        app(IntegrationAccess::class)->require($target, 'business.receive.'.$d['kind'], $enterprise->id);
        abort_if($target->id === $source->id || $target->expires_at?->isPast(), 422, '目标应用无效');
        abort_unless(DB::table('platform_application_grants')->where('application_id', $target->id)->where('enterprise_id', $enterprise->id)->where('employee_id', 0)->where('is_enabled', true)->exists(), 403, '接收应用尚未开通');
        $row = DB::transaction(function () use ($d, $source, $target, $enterprise) {
            DB::table('platform_business_messages')->insertOrIgnore(['uuid' => $d['uuid'], 'enterprise_id' => $enterprise->id, 'enterprise_uuid' => $enterprise->uuid, 'source_application_id' => $source->id, 'target_application_id' => $target->id, 'kind' => $d['kind'], 'correlation_uuid' => $d['correlation_uuid'], 'payload_json' => $d['payload_json'], 'payload_hash' => $d['payload_hash'], 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('platform_business_messages')->where('uuid', $d['uuid'])->lockForUpdate()->firstOrFail();
            abort_unless($row->enterprise_id === $enterprise->id && $row->source_application_id === $source->id && $row->target_application_id === $target->id && $row->kind === $d['kind'] && $row->correlation_uuid === $d['correlation_uuid'] && hash_equals($row->payload_hash, $d['payload_hash']), 409, '同一请求不可替换企业、目标或单据内容');

            return $row;
        });

        return $this->success(['message' => $row]);
    }

    public function index(Request $r): JsonResponse
    {
        $d = $r->validate(['enterprise_uuid' => 'required|uuid', 'kind' => ['required', Rule::in(self::KINDS)], 'after_id' => 'sometimes|integer|min:0']);
        [$app,$enterprise] = $this->authorizeApp($r, $d['enterprise_uuid'], 'business.receive.'.$d['kind']);
        $rows = DB::table('platform_business_messages')->select('platform_business_messages.*')->selectSub(PlatformApplication::query()->select('code')->whereColumn('id', 'platform_business_messages.source_application_id'), 'source_application_code')->where('target_application_id', $app->id)->where('enterprise_id', $enterprise->id)->where('kind', $d['kind'])->where('status', 'queued')->where('id', '>', $d['after_id'] ?? 0)->orderBy('id')->limit(100)->get();

        return $this->success(['items' => $rows, 'next_id' => $rows->last()?->id, 'has_more' => $rows->count() === 100]);
    }

    public function ack(Request $r, string $uuid): JsonResponse
    {
        $d = $r->validate(['enterprise_uuid' => 'required|uuid', 'result' => 'required|array']);
        $row = DB::table('platform_business_messages')->where('uuid', $uuid)->firstOrFail();
        [$app,$enterprise] = $this->authorizeApp($r, $d['enterprise_uuid'], 'business.receive.'.$row->kind);
        abort_unless($row->target_application_id === $app->id && $row->enterprise_id === $enterprise->id, 403);
        DB::transaction(function () use ($row, $d): void {
            $locked = DB::table('platform_business_messages')->where('id', $row->id)->lockForUpdate()->firstOrFail();
            $result = json_encode($d['result'], JSON_THROW_ON_ERROR);
            abort_if($locked->status === 'done' && json_decode($locked->result, true) != $d['result'], 409, '已确认结果不可替换');
            DB::table('platform_business_messages')->where('id', $row->id)->update(['status' => 'done', 'result' => $result, 'updated_at' => now()]);
        });

        return $this->success(['acknowledged' => true]);
    }
}
