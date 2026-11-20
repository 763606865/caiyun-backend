<?php

namespace App\SApi\Controllers;

use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Explicit identity registry; matching names or codes never merges existing business records. */
class MasterDataController extends Controller
{
    public const KINDS = ['customer', 'product', 'unit', 'warehouse'];

    /**
     * @return array{PlatformApplication,PlatformEnterprise}
     */
    private function access(Request $r, string $scope): array
    {
        $r->validate(['enterprise_uuid' => 'required|uuid']);

        return app(BusinessExchangeController::class)->authorizeApp($r, $r->string('enterprise_uuid')->toString(), $scope);
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['kind' => ['required', Rule::in(self::KINDS)], 'source_id' => 'required|string|max:100', 'version' => 'required|integer|min:1', 'payload' => 'required|array']);
        [$app,$enterprise] = $this->access($r, 'masters.write.'.$d['kind']);
        $rules = ['code' => 'required|string|max:100', 'name' => 'required|string|max:255', 'is_active' => 'required|boolean'];
        if ($d['kind'] === 'product') {
            $rules += ['unit_uuid' => 'required|uuid', 'specification' => 'nullable|string|max:255', 'trace_mode' => ['required', Rule::in(['none', 'lot', 'serial'])], 'expiry_tracked' => 'required|boolean'];
        }
        if ($d['kind'] === 'unit') {
            $rules += ['quantity_scale' => 'required|integer|between:0,6'];
        }
        if ($d['kind'] === 'customer') {
            $rules += ['credit_code' => 'nullable|string|max:100', 'address' => 'nullable|string|max:1000'];
        }
        $payload = validator($d['payload'], $rules)->validate();
        ksort($payload);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $json);
        if ($d['kind'] === 'product') {
            abort_unless(DB::table('platform_master_entities')->where('enterprise_id', $enterprise->id)->where('kind', 'unit')->where('uuid', $payload['unit_uuid'])->exists(), 422, '计量单位未在本企业登记');
        }
        $row = DB::transaction(function () use ($app, $enterprise, $d, $json, $hash) {
            DB::table('platform_master_entities')->insertOrIgnore(['uuid' => (string) Str::uuid7(), 'enterprise_id' => $enterprise->id, 'source_application_id' => $app->id, 'source_id' => $d['source_id'], 'kind' => $d['kind'], 'version' => $d['version'], 'payload' => $json, 'payload_hash' => $hash, 'created_at' => now(), 'updated_at' => now()]);
            $q = DB::table('platform_master_entities')->where('enterprise_id', $enterprise->id)->where('source_application_id', $app->id)->where('source_id', $d['source_id'])->where('kind', $d['kind']);
            $row = $q->lockForUpdate()->firstOrFail();
            abort_if($d['version'] < $row->version || ($d['version'] === $row->version && $hash !== $row->payload_hash), 409, '主数据旧版本或同版本内容冲突');
            if ($d['version'] > $row->version) {
                $q->update(['version' => $d['version'], 'payload' => $json, 'payload_hash' => $hash, 'updated_at' => now()]);
            }
            $this->bindLocal($app->id, $enterprise->id, $d['kind'], $row->uuid, $d['source_id']);

            return $q->firstOrFail();
        });

        return $this->success(['entity' => $row]);
    }

    public function index(Request $r): JsonResponse
    {
        [$app,$enterprise] = $this->access($r, 'masters.read');
        $d = $r->validate(['kind' => ['sometimes', Rule::in(self::KINDS)], 'after_id' => 'sometimes|integer|min:0']);
        $rows = DB::table('platform_master_entities')->where('enterprise_id', $enterprise->id)->when(isset($d['kind']), fn ($q) => $q->where('kind', $d['kind']))->where('id', '>', $d['after_id'] ?? 0)->orderBy('id')->limit(100)->get();

        return $this->success(['items' => $rows, 'next_id' => $rows->last()?->id, 'has_more' => $rows->count() === 100]);
    }

    public function bind(Request $r, string $uuid): JsonResponse
    {
        [$app,$enterprise] = $this->access($r, 'masters.bind');
        $d = $r->validate(['local_id' => 'required|string|max:100']);

        return DB::transaction(function () use ($app, $enterprise, $uuid, $d) {
            $row = DB::table('platform_master_entities')->where('enterprise_id', $enterprise->id)->where('uuid', $uuid)->lockForUpdate()->firstOrFail();
            $this->bindLocal($app->id, $enterprise->id, $row->kind, $uuid, $d['local_id']);

            return $this->success(['entity' => $row, 'local_id' => $d['local_id']]);
        });
    }

    private function bindLocal(int $app, int $enterprise, string $kind, string $uuid, string $localId): void
    {
        DB::table('platform_master_bindings')->insertOrIgnore(['application_id' => $app, 'enterprise_id' => $enterprise, 'kind' => $kind, 'entity_uuid' => $uuid, 'local_id' => $localId, 'created_at' => now(), 'updated_at' => now()]);
        abort_unless(DB::table('platform_master_bindings')->where('application_id', $app)->where('enterprise_id', $enterprise)->where('kind', $kind)->where('local_id', $localId)->where('entity_uuid', $uuid)->exists(), 409, '本地主数据已有映射，须人工核对冲突');
    }

    public function resolve(Request $r): JsonResponse
    {
        [$app,$enterprise] = $this->access($r, 'masters.read');
        $d = $r->validate(['uuids' => 'required|array|min:1|max:200', 'uuids.*' => 'required|uuid|distinct']);
        $rows = DB::table('platform_master_entities as e')->leftJoin('platform_master_bindings as b', function ($join) use ($app, $enterprise): void {
            $join->on('b.entity_uuid', '=', 'e.uuid')->where('b.application_id', $app->id)->where('b.enterprise_id', $enterprise->id);
        })
            ->where('e.enterprise_id', $enterprise->id)->whereIn('e.uuid', $d['uuids'])->get(['e.*', 'b.local_id']);
        abort_unless($rows->count() === count($d['uuids']), 422, '主数据不属于本企业或尚未登记');

        return $this->success(['items' => $rows]);
    }
}
