<?php

namespace App\Admin\Commands;

use App\Models\AdminUser;
use App\Services\Platform\IntegrationAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConfigureFulfillmentOperator extends Command
{
    protected $signature = 'platform:fulfillment-operator {enterprise : 中台企业 UUID} {admin : 后台管理员 ID} {--retry : 同时允许重试原消息} {--revoke : 撤销该企业工作台权限}';

    protected $description = '按单个企业显式发放或撤销履约异常与对账工作台权限';

    public function handle(IntegrationAccess $access): int
    {
        $uuid = $this->argument('enterprise');
        $id = $this->argument('admin');
        if (! is_string($uuid) || ! is_string($id) || ! ctype_digit($id)) {
            $this->error('请输入有效的企业 UUID 和数字管理员 ID');

            return self::INVALID;
        }
        $enterprise = $access->enterprise($uuid);
        $admin = AdminUser::query()->findOrFail((int) $id);
        if ($admin->hasRole('super-admin')) {
            $this->error('平台超级管理员已有全平台工作台权限');

            return self::FAILURE;
        }
        $identity = ['enterprise_id' => $enterprise->id, 'admin_user_id' => $admin->id];
        if ($this->option('revoke')) {
            DB::table('platform_fulfillment_operators')->where($identity)->delete();
        } else {
            DB::table('platform_fulfillment_operators')->upsert(
                [[...$identity, 'can_retry' => (bool) $this->option('retry')]], array_keys($identity), ['can_retry']
            );
        }
        $this->info('企业工作台权限已保存');

        return self::SUCCESS;
    }
}
