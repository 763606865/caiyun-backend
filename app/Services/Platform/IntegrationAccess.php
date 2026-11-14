<?php

namespace App\Services\Platform;

use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\Models\PlatformEnterprise;
use App\Models\PlatformOrganization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** 授权始终来自服务端配置，不接受调用方提交自己的 scope。 */
class IntegrationAccess
{
    public function allows(PlatformApplication $app, string $scope, int $enterprise = 0): bool
    {
        return DB::table('platform_application_scopes')->where('application_id', $app->id)
            ->where(function ($query) use ($scope, $enterprise): void {
                $query->where(fn ($q) => $q->where('scope', $scope)->where('enterprise_id', $enterprise))
                    ->orWhere(fn ($q) => $q->where('scope', 'platform.admin')->where('enterprise_id', 0));
            })->exists();
    }

    public function require(PlatformApplication $app, string $scope, int $enterprise = 0): void
    {
        abort_unless($this->allows($app, $scope, $enterprise), 403, '应用未获该企业接口授权');
    }

    public function enterprise(string $uuid): PlatformEnterprise
    {
        return PlatformEnterprise::query()->where('uuid', $uuid)->firstOrFail();
    }

    public function user(string $uuid): User
    {
        abort_unless(Str::isUuid($uuid), 422, '用户标识无效');

        return User::query()->where('uuid', hex2bin(str_replace('-', '', $uuid)))->firstOrFail();
    }

    /**
     * @param  array<string, string>  $data  Bound user, enterprise, employment and application identities.
     * @return array<string, mixed> Current authorization state; never a business-role grant.
     */
    public function check(PlatformApplication $app, array $data): array
    {
        abort_unless($data['application_code'] === $app->code, 403, '目标应用不匹配');
        $enterprise = $this->enterprise($data['enterprise_uuid']);
        $this->require($app, 'auth.check', $enterprise->id);
        $user = $this->user($data['user_uuid']);
        $employee = PlatformEmployee::query()->where('uuid', $data['employment_uuid'])
            ->where('enterprise_id', $enterprise->id)->where('user_id', $user->id)->firstOrFail();
        $grants = DB::table('platform_application_grants')->where('application_id', $app->id)->where('enterprise_id', $enterprise->id)
            ->whereIn('employee_id', [0, $employee->id])->get();
        $companyGrant = $grants->firstWhere('employee_id', 0);
        $personGrant = $grants->firstWhere('employee_id', $employee->id);
        $organization = $employee->organization_id ? PlatformOrganization::query()->find($employee->organization_id) : null;
        $organizationActive = ! $employee->organization_id || (bool) $organization;
        $seen = [];
        for ($node = $organization; $node; $node = $node->parent) {
            if (! $node->is_enabled || $node->enterprise_id !== $enterprise->id || isset($seen[$node->id])) {
                $organizationActive = false;
                break;
            }
            $seen[$node->id] = true;
        }
        $active = $app->is_enabled && ! $app->expires_at?->isPast() && $enterprise->is_enabled && $employee->is_enabled
            && $user->status->value === 1 && $organizationActive
            && $companyGrant?->is_enabled && $personGrant?->is_enabled;

        return [...$data, 'active' => (bool) $active, 'version' => (int) ($grants->sum('version') ?: 0),
            'reason' => $active ? 'active' : 'access_revoked', 'valid_until' => now()->addSeconds(60)->toIso8601String()];
    }

    public function audit(?PlatformApplication $app, string $action, ?int $enterprise, int $status, ?int $actor = null, ?string $key = null): void
    {
        DB::table('platform_integration_audits')->insert(['application_id' => $app?->id, 'enterprise_id' => $enterprise,
            'actor_user_id' => $actor, 'action' => substr($action, 0, 100), 'object_key' => $key, 'status' => $status,
            'trace_id' => substr((string) request()->header('X-Request-ID', Str::uuid()->toString()), 0, 100), 'created_at' => now()]);
    }
}
