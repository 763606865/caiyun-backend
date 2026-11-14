<?php

namespace App\SApi\Controllers;

use App\Services\Platform\DirectoryService;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DirectoryController extends Controller
{
    public function upsert(Request $request, DirectoryService $directory): JsonResponse
    {
        $data = $request->validate(['entity_type' => ['required', Rule::in(['enterprise', 'user', 'organization', 'employment'])],
            'source_system' => 'required|string|max:50', 'source_id' => 'required|string|max:191', 'source_version' => 'required|integer|min:1',
            'entity_uuid' => 'nullable|uuid', 'enterprise_uuid' => 'nullable|uuid', 'idempotency_key' => 'required|string|max:191', 'payload' => 'required|array']);

        return $this->success($directory->upsert($request->attributes->get('sapi_application'), $data));
    }

    public function snapshot(Request $request, IntegrationAccess $access, DirectoryService $directory): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'entity_type' => ['required', Rule::in(['enterprise', 'user', 'organization', 'employment'])], 'snapshot_id' => 'nullable|uuid', 'page' => 'sometimes|integer|min:1']);
        $app = $request->attributes->get('sapi_application');
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'directory.read', $enterprise->id);
        if (! empty($data['snapshot_id'])) {
            $snapshot = DB::table('platform_directory_snapshots')->where('id', $data['snapshot_id'])->where('application_id', $app->id)->where('enterprise_id', $enterprise->id)->where('entity_type', $data['entity_type'])->where('expires_at', '>', now())->first();
            abort_unless($snapshot !== null, 409, '快照已失效，请重建');
        } else {
            $snapshot = Cache::lock('platform-directory-write', 30)->block(10, function () use ($app, $enterprise, $data, $directory) {
                return DB::transaction(function () use ($app, $enterprise, $data, $directory) {
                    $row = ['id' => (string) Str::uuid7(), 'application_id' => $app->id, 'enterprise_id' => $enterprise->id,
                        'entity_type' => $data['entity_type'], 'cursor' => (int) DB::table('platform_directory_changes')->max('id'),
                        'items' => json_encode($directory->items($data['entity_type'], $enterprise)), 'expires_at' => now()->addMinutes(30)];
                    DB::table('platform_directory_snapshots')->insert($row);

                    return (object) $row;
                });
            });
        }
        $items = array_map(fn ($item) => [...$item, 'version' => (int) $snapshot->cursor], json_decode($snapshot->items, true));
        $page = $data['page'] ?? 1;

        return $this->success(['snapshot_id' => $snapshot->id, 'items' => array_slice($items, ($page - 1) * 100, 100), 'cursor' => $snapshot->cursor,
            'next_page' => count($items) > $page * 100 ? $page + 1 : null]);
    }

    public function changes(Request $request, IntegrationAccess $access): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'cursor' => 'required|integer|min:0']);
        $app = $request->attributes->get('sapi_application');
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'directory.read', $enterprise->id);
        $rows = DB::table('platform_directory_changes')->where('enterprise_id', $enterprise->id)->where('id', '>', $data['cursor'])->orderBy('id')->limit(100)->get();

        return $this->success(['items' => $rows->map(fn ($row) => ['version' => $row->id, 'entity_type' => $row->entity_type, 'entity_uuid' => $row->entity_uuid,
            'payload' => json_decode($row->payload, true), 'occurred_at' => $row->created_at])->all(), 'next_cursor' => $rows->isEmpty() ? (int) $data['cursor'] : $rows->last()->id, 'has_more' => $rows->count() === 100]);
    }

    public function resolve(Request $request, IntegrationAccess $access, DirectoryService $directory): JsonResponse
    {
        $data = $request->validate(['enterprise_uuid' => 'required|uuid', 'entity_type' => ['required', Rule::in(['enterprise', 'user', 'organization', 'employment'])],
            'source_id' => 'nullable|string|max:191', 'entity_uuid' => 'nullable|uuid']);
        $app = $request->attributes->get('sapi_application');
        $enterprise = $access->enterprise($data['enterprise_uuid']);
        $access->require($app, 'directory.read', $enterprise->id);
        $query = DB::table('platform_directory_sources')->where('application_id', $app->id)->where('enterprise_id', $enterprise->id)->where('entity_type', $data['entity_type']);
        abort_unless(! empty($data['entity_uuid']) || ! empty($data['source_id']), 422, '需提供稳定查询键');
        $source = ! empty($data['source_id']) ? $query->where('source_id', $data['source_id'])->first() : $query->where('entity_uuid', $data['entity_uuid'])->first();
        abort_unless($source !== null, 404, '映射不存在');

        return $this->success(['entity_uuid' => $source->entity_uuid, 'source_version' => $source->source_version, 'entity_type' => $source->entity_type, 'enterprise_uuid' => $enterprise->uuid]);
    }
}
