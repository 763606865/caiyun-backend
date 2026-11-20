<?php

namespace App\Admin\Commands;

use App\Admin\Commands\Concerns\ReadsIntegrationOptions;
use App\Models\PlatformApplication;
use App\Models\PlatformEmployee;
use App\SApi\Controllers\BusinessExchangeController;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** 显式本地运维入口：不输出凭证，不自动给所有企业授权。 */
class ConfigurePlatformIntegration extends Command
{
    use ReadsIntegrationOptions;

    protected $signature = 'platform:integration {application : 已注册应用编码} {--enterprise= : 中台企业UUID} {--scope=* : directory.read/write、auth.check/delegate、contracts.submit/receive、subscription.read/write、grants.manage、directory.bootstrap、platform.admin} {--employment= : 任职UUID} {--grant : 开通企业或任职的应用权限} {--revoke : 撤销指定scope或应用授权} {--redirect-uri=* : 登录回跳白名单，传入时替换整份名单}';

    protected $description = '按应用和企业显式配置中台接入权限与登录白名单';

    public function handle(IntegrationAccess $access): int
    {
        $enterpriseUuid = $this->stringOption('enterprise');
        $employmentUuid = $this->stringOption('employment');
        $scopes = $this->stringListOption('scope');
        $redirectUris = $this->stringListOption('redirect-uri');
        $grant = $this->option('grant') === true;
        $revoke = $this->option('revoke') === true;
        $app = PlatformApplication::query()->where('code', $this->stringArgument('application'))->firstOrFail();
        $enterprise = $enterpriseUuid ? $access->enterprise($enterpriseUuid) : null;
        foreach ($scopes as $scope) {
            $businessScopes = array_merge(
                array_map(fn (string $kind): string => 'business.send.'.$kind, BusinessExchangeController::KINDS),
                array_map(fn (string $kind): string => 'business.receive.'.$kind, BusinessExchangeController::KINDS),
            );
            if (! in_array($scope, array_merge(['directory.read', 'directory.write', 'auth.check', 'auth.delegate', 'contracts.submit', 'contracts.receive', 'subscription.read', 'subscription.write', 'grants.manage', 'directory.bootstrap', 'platform.admin', 'masters.read', 'masters.bind', 'masters.write.customer', 'masters.write.unit', 'masters.write.product', 'masters.write.warehouse', 'fulfillment.observe', 'fulfillment.retry'], $businessScopes), true)) {
                $this->error('未知 scope');

                return self::FAILURE;
            }
            if (! $enterprise && ! in_array($scope, ['directory.bootstrap', 'platform.admin'], true)) {
                $this->error('该 scope 必须指定企业');

                return self::FAILURE;
            }
        }
        if ($employmentUuid && (! $enterprise || ! $grant)) {
            $this->error('任职授权需同时指定 enterprise 与 grant');

            return self::FAILURE;
        }
        if ($grant && ! $enterprise) {
            $this->error('应用开通必须指定企业');

            return self::FAILURE;
        }
        foreach ($redirectUris as $uri) {
            if (! filter_var($uri, FILTER_VALIDATE_URL) || ! in_array(parse_url($uri, PHP_URL_SCHEME), ['https', 'http'], true)) {
                $this->error('回跳地址必须为完整 HTTP(S) URL');

                return self::FAILURE;
            }
        }
        $enterpriseId = $enterprise === null ? 0 : $enterprise->id;
        DB::transaction(function () use ($app, $enterpriseId, $access, $scopes, $redirectUris, $employmentUuid, $grant, $revoke): void {
            foreach ($scopes as $scope) {
                $keys = ['application_id' => $app->id, 'enterprise_id' => $enterpriseId, 'scope' => $scope];
                if ($revoke) {
                    DB::table('platform_application_scopes')->where($keys)->delete();
                } else {
                    DB::table('platform_application_scopes')->insertOrIgnore($keys);
                }
            }
            if ($grant) {
                $employee = $employmentUuid ? PlatformEmployee::query()->where('enterprise_id', $enterpriseId)->where('uuid', $employmentUuid)->firstOrFail() : null;
                $keys = ['application_id' => $app->id, 'enterprise_id' => $enterpriseId, 'employee_id' => $employee === null ? 0 : $employee->id];
                DB::table('platform_application_grants')->insertOrIgnore([...$keys, 'is_enabled' => false, 'version' => 0, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('platform_application_grants')->where($keys)->update(['is_enabled' => ! $revoke, 'version' => DB::raw('version + 1'), 'updated_at' => now()]);
            }
            if ($redirectUris) {
                $app->forceFill(['redirect_uris' => array_values(array_unique($redirectUris))])->save();
            }
            $access->audit($app, 'operator.configure', $enterpriseId === 0 ? null : $enterpriseId, 200);
        });
        $this->info('配置已保存；未显示应用密钥。');

        return self::SUCCESS;
    }
}
