<?php

namespace App\Services\Platform;

use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DirectoryService
{
    public static bool $writing = false;

    public function __construct(private IntegrationAccess $access) {}

    /**
     * @param  array<string, mixed>  $data  Validated directory command.
     * @return array<string, mixed> Stable identity and source/change versions.
     */
    public function upsert(PlatformApplication $app, array $data): array
    {
        abort_unless($app->code === $data['source_system'], 403, '来源应用不匹配');
        $enterprise = isset($data['enterprise_uuid']) ? $this->access->enterprise($data['enterprise_uuid']) : null;
        if (! $enterprise) {
            abort_unless($data['entity_type'] === 'enterprise', 422, '必须提供所属企业');
            $this->access->require($app, 'directory.bootstrap');
        } else {
            $this->access->require($app, 'directory.write', $enterprise->id);
        }
        $hash = hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return Cache::lock('platform-directory-write', 30)->block(10, function () use ($app, $data, $enterprise, $hash): array {
            return DB::transaction(function () use ($app, $data, $enterprise, $hash): array {
                $prior = DB::table('platform_integration_requests')->where('application_id', $app->id)->where('idempotency_key', $data['idempotency_key'])->first();
                if ($prior) {
                    abort_unless(hash_equals($prior->request_hash, $hash), 409, '幂等键内容冲突');

                    return json_decode($prior->result, true);
                }
                $source = DB::table('platform_directory_sources')->where('application_id', $app->id)->where('entity_type', $data['entity_type'])->where('source_id', $data['source_id'])->lockForUpdate()->first();
                $payloadHash = hash('sha256', json_encode([$data['payload'], $data['entity_uuid'] ?? null], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                if ($source) {
                    abort_unless(! $enterprise || $source->enterprise_id === $enterprise->id || $data['entity_type'] === 'user', 409, '来源企业映射冲突');
                    abort_unless(! isset($data['entity_uuid']) || $source->entity_uuid === $data['entity_uuid'], 409, '统一身份映射冲突');
                    abort_unless($data['source_version'] > $source->source_version || ($data['source_version'] === $source->source_version && hash_equals($source->payload_hash, $payloadHash)), 409, '旧版本或同版本内容冲突');
                }
                $uuid = $source->entity_uuid ?? ($data['entity_uuid'] ?? null);
                $record = $uuid ? $this->record($data['entity_type'], $uuid) : null;
                if ($record instanceof PlatformEnterprise && ! $enterprise) {
                    $this->access->require($app, 'directory.write', $record->id);
                }
                // 显式绑定仅限当前企业已有记录。用户需已属于企业或为本应用已创建的用户。
                if ($record && $enterprise && ! $source) {
                    if ($data['entity_type'] === 'user') {
                        abort_unless(PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->where('user_id', $record->id)->exists()
                            || DB::table('platform_directory_sources')->where('application_id', $app->id)->where('entity_type', 'user')->where('entity_uuid', $uuid)->exists(), 403, '用户绑定需先由运维确认');
                    } elseif ($data['entity_type'] === 'enterprise') {
                        abort_unless($record->id === $enterprise->id, 422, '企业绑定不匹配');
                    } elseif ($record instanceof PlatformOrganization || $record instanceof PlatformEmployee) {
                        abort_unless($record->enterprise_id === $enterprise->id, 422, '记录不属于企业');
                    }
                }
                $record = $this->save($data['entity_type'], $data['payload'], $enterprise, $record, $app);
                if ($record instanceof PlatformEnterprise) {
                    $enterprise = $record;
                    if (! $source) {
                        foreach (['directory.read', 'directory.write', 'auth.check'] as $scope) {
                            DB::table('platform_application_scopes')->insertOrIgnore(['application_id' => $app->id, 'enterprise_id' => $enterprise->id, 'scope' => $scope]);
                        }
                    }
                }
                if ($enterprise === null) {
                    throw new \LogicException('Directory enterprise missing after save.');
                }
                $sourceData = ['enterprise_id' => $enterprise->id, 'entity_uuid' => $record->uuid, 'source_version' => $data['source_version'], 'payload_hash' => $payloadHash];
                DB::table('platform_directory_sources')->updateOrInsert(['application_id' => $app->id, 'entity_type' => $data['entity_type'], 'source_id' => $data['source_id']], $sourceData);
                $version = DB::table('platform_directory_changes')->insertGetId(['enterprise_id' => $enterprise->id, 'entity_type' => $data['entity_type'], 'entity_uuid' => $record->uuid, 'payload' => json_encode($this->project($data['entity_type'], $record, $enterprise->uuid)), 'created_at' => now()]);
                $result = ['entity_uuid' => $record->uuid, 'entity_type' => $data['entity_type'], 'enterprise_uuid' => $enterprise->uuid, 'version' => $version, 'source_version' => $data['source_version']];
                DB::table('platform_integration_requests')->insert(['application_id' => $app->id, 'idempotency_key' => $data['idempotency_key'], 'request_hash' => $hash, 'result' => json_encode($result)]);

                return $result;
            });
        });
    }

    public function record(string $type, string $uuid): PlatformEnterprise|PlatformOrganization|PlatformEmployee|User
    {
        return match ($type) {
            'enterprise' => PlatformEnterprise::query()->where('uuid', $uuid)->firstOrFail(),
            'organization' => PlatformOrganization::query()->where('uuid', $uuid)->firstOrFail(),
            'employment' => PlatformEmployee::query()->where('uuid', $uuid)->firstOrFail(),
            'user' => $this->access->user($uuid),
            default => throw new \InvalidArgumentException('Unsupported directory type.'),
        };
    }

    /** @param array<string, mixed> $payload */
    private function save(string $type, array $payload, ?PlatformEnterprise $enterprise, PlatformEnterprise|PlatformOrganization|PlatformEmployee|User|null $record, PlatformApplication $app): PlatformEnterprise|PlatformOrganization|PlatformEmployee|User
    {
        $required = $record ? 'sometimes' : 'required';
        $rules = match ($type) {
            'enterprise' => ['code' => [$required, 'string', 'max:255', Rule::unique('platform_enterprises')->ignore($record?->id)], 'name' => [$required, 'string', 'max:255'], 'short_name' => ['nullable', 'string', 'max:255'], 'credit_code' => ['nullable', 'string', 'max:255'], 'contact_name' => ['nullable', 'string', 'max:255'], 'contact_mobile' => ['nullable', 'string', 'max:255'], 'is_enabled' => ['boolean']],
            'user' => ['phone' => [$required, 'string', 'max:50', Rule::unique('users')->ignore($record?->id)], 'real_name' => ['nullable', 'string', 'max:255'], 'nick_name' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'status' => ['integer', Rule::in([0, 1])]],
            'organization' => ['code' => [$required, 'string', 'max:255', Rule::unique('platform_organizations')->ignore($record?->id)], 'name' => [$required, 'string', 'max:255'], 'parent_uuid' => ['nullable', 'uuid'], 'is_enabled' => ['boolean']],
            'employment' => ['user_uuid' => [$required, 'uuid'], 'organization_uuid' => ['nullable', 'uuid'], 'employee_no' => ['nullable', 'string', 'max:255', Rule::unique('platform_employees')->where('enterprise_id', $enterprise?->id)->ignore($record?->id)], 'position' => ['nullable', 'string', 'max:255'], 'is_enabled' => ['boolean']],
            default => throw new \InvalidArgumentException('Unsupported directory type.'),
        };
        abort_if(array_diff(array_keys($payload), array_keys($rules)) !== [], 422, '目录包含未允许字段');
        $values = Validator::make($payload, $rules)->validate();
        if ($type !== 'enterprise' && $enterprise === null) {
            throw new \LogicException('Directory objects require an enterprise.');
        }
        if (in_array($type, ['organization', 'employment'])) {
            $values['enterprise_id'] = $enterprise->id;
        }
        if ($type === 'organization' && array_key_exists('parent_uuid', $values)) {
            $parent = $values['parent_uuid'] ? PlatformOrganization::query()->where('uuid', $values['parent_uuid'])->where('enterprise_id', $enterprise->id)->firstOrFail() : null;
            $seen = [];
            for ($node = $parent; $node; $node = $node->parent) {
                abort_if($node->id === $record?->id || isset($seen[$node->id]), 422, '组织层级形成循环');
                $seen[$node->id] = true;
            }
            $values['parent_id'] = $parent?->id;
            unset($values['parent_uuid']);
        }
        if ($type === 'employment') {
            if (isset($values['user_uuid'])) {
                $user = $this->access->user($values['user_uuid']);
                abort_unless(PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->where('user_id', $user->id)->exists()
                    || DB::table('platform_directory_sources')->where('application_id', $app->id)->where('entity_type', 'user')->where('entity_uuid', $user->uuid)->exists(), 403, '用户不在来源授权目录');
                // 不得通过任职接口替换已有任职身份。
                abort_if($record instanceof PlatformEmployee && $record->user_id !== $user->id, 422, '任职用户不可变');
                $values['user_id'] = $user->id;
                unset($values['user_uuid']);
            }
            if (array_key_exists('organization_uuid', $values)) {
                $values['organization_id'] = $values['organization_uuid'] ? PlatformOrganization::query()->where('uuid', $values['organization_uuid'])->where('enterprise_id', $enterprise->id)->firstOrFail()->id : null;
                unset($values['organization_uuid']);
            }
            if (! $record) {
                abort_if(PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->where('user_id', $values['user_id'])->exists(), 409, '任职已存在，请显式绑定');
            }
        }
        if (! $record && $type === 'user') {
            $values['password'] = Str::random(64);
        }
        $record ??= match ($type) {
            'enterprise' => new PlatformEnterprise,
            'organization' => new PlatformOrganization,
            'user' => new User,
            default => new PlatformEmployee, // The validation match above already rejects unknown types.
        };
        self::$writing = true;
        try {
            $record->fill($values)->save();
        } finally {
            self::$writing = false;
        }

        return $record->refresh();
    }

    /** @return array<string, mixed> Minimal cross-application directory projection. */
    public function project(string $type, PlatformEnterprise|PlatformOrganization|PlatformEmployee|User $record, string $enterpriseUuid): array
    {
        $data = ['entity_type' => $type, 'entity_uuid' => $record->uuid, 'enterprise_uuid' => $enterpriseUuid];

        return [...$data, ...match (true) {
            $record instanceof PlatformEnterprise => $record->only(['code', 'name', 'short_name', 'is_enabled']),
            $record instanceof User => $record->only(['real_name', 'nick_name', 'status']),
            $record instanceof PlatformOrganization => [...$record->only(['code', 'name', 'is_enabled']), 'parent_uuid' => $record->parent?->uuid],
            $record instanceof PlatformEmployee => [...$record->only(['employee_no', 'position', 'is_enabled']), 'user_uuid' => $record->user->uuid,
                'organization_uuid' => $record->organization?->uuid],
        }];
    }

    /** @return list<array<string, mixed>> */
    public function items(string $type, PlatformEnterprise $enterprise): array
    {
        $records = match ($type) {
            'enterprise' => collect([$enterprise]),
            'organization' => PlatformOrganization::query()->with('parent')->where('enterprise_id', $enterprise->id)->orderBy('id')->get(),
            'employment' => PlatformEmployee::query()->with(['user', 'organization'])->where('enterprise_id', $enterprise->id)->orderBy('id')->get(),
            'user' => User::query()->whereIn('id', PlatformEmployee::query()->where('enterprise_id', $enterprise->id)->select('user_id'))->orderBy('id')->get(),
            default => throw new \InvalidArgumentException('Unsupported directory type.'),
        };

        return array_values($records->map(fn ($record) => $this->project($type, $record, $enterprise->uuid))->all());
    }
}
