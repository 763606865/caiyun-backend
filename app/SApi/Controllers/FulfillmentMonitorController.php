<?php

namespace App\SApi\Controllers;

use App\Services\Platform\FulfillmentReconciliation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FulfillmentMonitorController extends Controller
{
    private const OBSERVE = 'fulfillment.observe';

    private const RETRY = 'fulfillment.retry';

    public function report(Request $request, BusinessExchangeController $exchange, FulfillmentReconciliation $reconciliation): JsonResponse
    {
        $input = $request->validate([
            'enterprise_uuid' => 'required|uuid',
            'topic' => 'required|in:failure,inventory,finance,production',
            'records' => 'required|array|min:1|max:50',
            'observed_at' => 'required|date',
        ]);
        [$app, $enterprise] = $exchange->authorizeApp($request, $input['enterprise_uuid'], self::OBSERVE, exactScope: true);
        $observed = Carbon::parse($input['observed_at']);
        abort_if($observed->greaterThan(now()->addMinutes(2)) || $observed->lessThan(now()->subHour()), 422, '观测时间超出允许范围');
        $timestamp = $observed->utc()->format('Y-m-d H:i:s.v');
        $rules = $input['topic'] === 'failure' ? $this->failureRules() : $this->evidenceRules($input['topic']);
        $validated = Validator::make(['records' => $input['records']], $rules)->validate();

        foreach ($validated['records'] as $record) {
            if ($input['topic'] === 'failure') {
                $this->storeFailure($request, $exchange, $app->id, $enterprise->id, $input['enterprise_uuid'], $record, $timestamp);
            } else {
                $this->storeEvidence($reconciliation, $app->id, $enterprise->id, $input['topic'], $record, $timestamp);
            }
        }

        return $this->success(['accepted' => count($validated['records'])]);
    }

    public function retries(Request $request, BusinessExchangeController $exchange): JsonResponse
    {
        $input = $request->validate([
            'enterprise_uuid' => 'required|uuid',
            'direction' => 'required|in:send,receive',
            'kind' => [Rule::requiredIf($request->input('direction') === 'receive'), Rule::in(BusinessExchangeController::KINDS)],
        ]);
        [$app, $enterprise] = $exchange->authorizeApp($request, $input['enterprise_uuid'], self::RETRY, exactScope: true);
        $rows = DB::table('platform_fulfillment_retries')
            ->where('enterprise_id', $enterprise->id)->where('application_id', $app->id)
            ->where('direction', $input['direction'])->where('status', 'queued')
            ->when(isset($input['kind']), fn ($query) => $query->where('kind', $input['kind']))
            ->orderBy('id')->limit(25)->get(['uuid', 'message_uuid']);

        return $this->success(['items' => $rows]);
    }

    public function finish(Request $request, string $uuid, BusinessExchangeController $exchange): JsonResponse
    {
        $input = $request->validate(['enterprise_uuid' => 'required|uuid']);
        [$app, $enterprise] = $exchange->authorizeApp($request, $input['enterprise_uuid'], self::RETRY, exactScope: true);
        DB::transaction(function () use ($uuid, $enterprise, $app): void {
            $retry = DB::table('platform_fulfillment_retries')->where('uuid', $uuid)
                ->where('enterprise_id', $enterprise->id)->where('application_id', $app->id)->lockForUpdate()->firstOrFail();
            if ($retry->status !== 'queued') {
                return;
            }
            $status = DB::table('platform_business_messages')->where('enterprise_id', $enterprise->id)
                ->where('uuid', $retry->message_uuid)->where('correlation_uuid', $retry->correlation_uuid)
                ->where('kind', $retry->kind)
                ->where($retry->direction === 'send' ? 'source_application_id' : 'target_application_id', $app->id)->value('status');
            $completed = $retry->direction === 'send' ? $status !== null : $status === 'done';
            DB::table('platform_fulfillment_retries')->where('id', $retry->id)->update([
                'status' => $completed ? 'completed' : 'failed',
                'finished_at' => now(),
            ]);
        }, 3);

        return $this->success(['recorded' => true]);
    }

    /** @return array<string, mixed> */
    private function failureRules(): array
    {
        return [
            'records.*' => 'required|array:uuid,correlation_uuid,kind,direction,reason,attempts,next_attempt_at',
            'records.*.uuid' => 'required|uuid',
            'records.*.direction' => 'required|in:send,receive',
            'records.*.correlation_uuid' => 'required_if:records.*.direction,send|nullable|uuid',
            'records.*.kind' => ['required_if:records.*.direction,send', 'nullable', Rule::in(BusinessExchangeController::KINDS)],
            'records.*.reason' => 'required|in:access,conflict,validation,unavailable,throttled,missing,unknown',
            'records.*.attempts' => 'nullable|integer|min:1|max:4294967295',
            'records.*.next_attempt_at' => 'nullable|date',
        ];
    }

    /** @return array<string, mixed> */
    private function evidenceRules(string $kind): array
    {
        $common = [
            'records.*.uuid' => 'required|uuid',
            'records.*.document_uuid' => 'nullable|uuid',
            'records.*.document_number' => 'nullable|string|max:100',
        ];
        $numeric = 'required|regex:/^-?\d{1,24}(?:\.\d{1,6})?$/';
        if ($kind === 'finance') {
            $amounts = array_keys(FulfillmentReconciliation::AMOUNTS);

            return [...$common,
                'records.*' => 'required|array:uuid,side,version,complete,currency,amounts,document_uuid,document_number,status',
                'records.*.side' => 'required|in:left,right',
                'records.*.version' => 'required|integer|min:1',
                'records.*.status' => 'nullable|in:draft,pending,approved,rejected,cancelled,posted',
                'records.*.complete' => 'required|boolean',
                'records.*.currency' => 'required|regex:/^[A-Z]{3}$/',
                'records.*.amounts' => 'required|array:'.implode(',', $amounts),
                ...array_fill_keys(array_map(fn (string $key): string => 'records.*.amounts.'.$key, $amounts), $numeric),
            ];
        }

        if ($kind === 'production') {
            return [...$common,
                'records.*' => 'required|array:uuid,status,document_uuid,document_number,operator_id',
                'records.*.status' => 'required|in:draft,released,paused,closed',
                'records.*.operator_id' => 'nullable|integer|min:1',
            ];
        }

        return [...$common,
            'records.*' => 'required|array:uuid,side,status,complete,warehouse_uuid,quantities,document_uuid,document_number,operator_id',
            'records.*.side' => 'required|in:left,right',
            'records.*.status' => 'required|in:draft,approved,submitted,processing,posted,cancelled,pending,rejected',
            'records.*.complete' => 'required|boolean',
            'records.*.warehouse_uuid' => 'nullable|uuid',
            'records.*.quantities' => 'present|array|max:100',
            'records.*.quantities.*' => $numeric,
            'records.*.operator_id' => 'nullable|integer|min:1',
        ];
    }

    /** @param array<string, mixed> $record */
    private function storeFailure(Request $request, BusinessExchangeController $exchange, int $application, int $enterprise, string $enterpriseUuid, array $record, string $timestamp): void
    {
        $sending = $record['direction'] === 'send';
        $message = DB::table('platform_business_messages')->where('enterprise_id', $enterprise)->where('uuid', $record['uuid'])->first();
        if ($message !== null) {
            abort_unless($message->{$sending ? 'source_application_id' : 'target_application_id'} === $application, 403);
            if ($sending || $message->status === 'done') {
                return;
            }
        }
        abort_if(! $sending && $message === null, 404);
        $kind = $message->kind ?? $record['kind'];
        $root = $message->correlation_uuid ?? $record['correlation_uuid'];
        $exchange->authorizeApp($request, $enterpriseUuid, 'business.'.$record['direction'].'.'.$kind);
        $this->writeFailure($enterprise, $application, $root, $kind, $record, $timestamp);
    }

    /** @param array<string, mixed> $record */
    private function writeFailure(int $enterprise, int $application, string $root, string $kind, array $record, string $timestamp): void
    {
        $identity = ['enterprise_id' => $enterprise, 'application_id' => $application, 'message_uuid' => $record['uuid'], 'direction' => $record['direction']];
        $values = [
            'correlation_uuid' => $root, 'kind' => $kind, 'reason' => $record['reason'],
            'attempts' => $record['attempts'] ?? null,
            'next_attempt_at' => isset($record['next_attempt_at']) ? Carbon::parse($record['next_attempt_at'])->utc()->toDateTimeString() : null,
            'observed_at' => $timestamp,
        ];
        DB::transaction(function () use ($identity, $values, $timestamp): void {
            DB::table('platform_fulfillment_failures')->insertOrIgnore([...$identity, ...$values]);
            DB::table('platform_fulfillment_failures')->where($identity)->where('observed_at', '<=', $timestamp)->update($values);
        }, 3);
    }

    /** @param array<string, mixed> $record */
    private function storeEvidence(FulfillmentReconciliation $reconciliation, int $application, int $enterprise, string $kind, array $record, string $timestamp): void
    {
        $side = $record['side'] ?? 'left';
        $query = DB::table('platform_business_messages')->where('enterprise_id', $enterprise);
        $anchor = match ($kind) {
            'inventory' => $query->where('uuid', $record['uuid'])->whereIn('kind', ['warehouse_delivery', 'warehouse_receipt'])->firstOrFail(),
            'finance' => $query->where('correlation_uuid', $record['uuid'])->where('kind', 'sales_order')->orderByDesc('id')->firstOrFail(),
            'production' => $query->where('uuid', $record['uuid'])->where('kind', 'production_order')->firstOrFail(),
            default => abort(422, '未知对账类型'),
        };
        $source = $side === 'right';
        // Inventory left = WMS, finance left = ERP, production left = MES. Each left peer receives its anchor.
        abort_unless(($source ? $anchor->source_application_id : $anchor->target_application_id) === $application, 403);
        if ($kind === 'inventory') {
            foreach (array_keys($record['quantities']) as $key) {
                abort_unless(Str::isUuid((string) $key), 422, '数量明细必须使用中台物料 UUID');
            }
            abort_if($record['complete'] && $record['status'] === 'posted' && (empty($record['quantities']) || empty($record['warehouse_uuid'])), 422, '已过账证据缺少流水或仓库');
        }
        $identity = ['enterprise_id' => $enterprise, 'kind' => $kind, 'subject_uuid' => $record['uuid']];
        unset($record['uuid'], $record['side']);
        if (isset($record['complete'])) {
            $record['complete'] = (bool) $record['complete'];
        }
        if (isset($record['version'])) {
            $record['version'] = (int) $record['version'];
        }
        DB::transaction(function () use ($identity, $anchor, $side, $record, $timestamp, $reconciliation, $kind): void {
            DB::table('platform_fulfillment_checks')->insertOrIgnore([
                ...$identity, 'correlation_uuid' => $anchor->correlation_uuid, 'subtype' => $anchor->kind,
                'status' => 'awaiting', 'status_since' => now(),
            ]);
            $row = DB::table('platform_fulfillment_checks')->where($identity)->lockForUpdate()->firstOrFail();
            $snapshot = $side.'_snapshot';
            $observedAt = $side.'_observed_at';
            if ($row->{$observedAt} !== null && Carbon::parse($row->{$observedAt}, 'UTC')->greaterThan(Carbon::parse($timestamp, 'UTC'))) {
                return;
            }
            $other = $row->{$side === 'left' ? 'right_snapshot' : 'left_snapshot'};
            $other = $other === null ? null : json_decode($other, true, 32, JSON_THROW_ON_ERROR);
            $status = $side === 'left' ? $reconciliation->status($kind, $record, $other) : $reconciliation->status($kind, $other, $record);
            DB::table('platform_fulfillment_checks')->where('id', $row->id)->update([
                $snapshot => json_encode($record, JSON_THROW_ON_ERROR),
                $observedAt => $timestamp,
                'status' => $status,
                'status_since' => $row->status === $status ? $row->status_since : now(),
            ]);
        }, 3);
    }
}
