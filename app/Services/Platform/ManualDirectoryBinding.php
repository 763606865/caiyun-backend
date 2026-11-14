<?php

namespace App\Services\Platform;

use App\Models\AdminUser;
use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformOrganization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** 人工绑定入口：仅超级管理员，默认只预检，永不覆盖既有映射。 */
class ManualDirectoryBinding
{
    /**
     * @param  array<string, mixed>  $data  Human-confirmed binding form values.
     * @return array{applied: bool, already_bound: bool, entity_uuid: string, enterprise_uuid: string}
     */
    public function execute(AdminUser $actor, array $data): array
    {
        abort_unless($actor->is_active && $actor->hasRole('super-admin'), 403, '需要平台超级管理员权限');
        $validated = validator($data, ['application_id' => 'required|integer|exists:platform_applications,id',
            'enterprise_uuid' => 'required|uuid', 'entity_type' => 'required|in:enterprise,user,organization,employment',
            'source_id' => 'required|string|max:191', 'entity_uuid' => 'required|uuid', 'apply' => 'required|boolean'])->validate();
        $app = PlatformApplication::query()->whereKey($validated['application_id'])->firstOrFail();
        $access = app(IntegrationAccess::class);
        $enterprise = $access->enterprise($validated['enterprise_uuid']);
        $key = substr('admin:'.$actor->id.':'.$validated['entity_type'].':'.$validated['source_id'], 0, 191);

        return Cache::lock('platform-directory-write', 30)->block(10, function () use ($validated, $app, $access, $enterprise, $key): array {
            try {
                return DB::transaction(function () use ($validated, $app, $access, $enterprise, $key): array {
                    $record = app(DirectoryService::class)->record($validated['entity_type'], $validated['entity_uuid']);
                    $type = $validated['entity_type'];
                    if (($type === 'enterprise' && $record->id !== $enterprise->id)
                        || (($record instanceof PlatformOrganization || $record instanceof PlatformEmployee) && $record->enterprise_id !== $enterprise->id)) {
                        throw ValidationException::withMessages(['entity_uuid' => '目标记录不属于所选企业，禁止跨企业绑定']);
                    }
                    $keys = ['application_id' => $app->id, 'entity_type' => $type, 'source_id' => $validated['source_id']];
                    $existing = DB::table('platform_directory_sources')->where($keys)->lockForUpdate()->first();
                    if ($existing && ($existing->entity_uuid !== $record->uuid || $existing->enterprise_id !== $enterprise->id)) {
                        throw ValidationException::withMessages(['source_id' => '来源已绑定其他身份或企业，禁止覆盖；请核查原绑定']);
                    }
                    if ($validated['apply'] && ! $existing) {
                        DB::table('platform_directory_sources')->insert([...$keys, 'enterprise_id' => $enterprise->id, 'entity_uuid' => $record->uuid, 'source_version' => 0, 'payload_hash' => hash('sha256', '')]);
                    }
                    $access->audit($app, $validated['apply'] ? 'admin.directory.bind' : 'admin.directory.preflight', $enterprise->id, 200, null, $key);

                    return ['applied' => $validated['apply'], 'already_bound' => (bool) $existing, 'entity_uuid' => $record->uuid, 'enterprise_uuid' => $enterprise->uuid];
                });
            } catch (\Throwable $exception) {
                // 独立于回滚事务记录拒绝结果，不能因为冲突回滚而丢失审计。
                $access->audit($app, 'admin.directory.bind.rejected', $enterprise->id, 409, null, $key);
                throw $exception;
            }
        });
    }
}
