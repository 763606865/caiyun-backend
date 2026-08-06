<?php

namespace App\Admin\Commands;

use App\Models\AdminUser;
use App\Models\LicenseInstance;
use App\Models\LicensePackage;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class DetectAbnormalLicenseHeartbeats extends Command
{
    protected $signature = 'license:detect-abnormal {--days= : 覆盖默认失联天数}';

    protected $description = '检测超过指定天数未成功验证的授权实例并通知管理员';

    public function handle(): int
    {
        $days = max(1, (int) ($this->option('days') ?: config('licensing.heartbeat_abnormal_days', 7)));
        $instances = LicenseInstance::query()
            ->with('licensePackage')
            ->where('last_verified_at', '<', now()->subDays($days))
            ->where('status', '!=', 'abnormal')
            ->get();

        $admins = AdminUser::query()->where('is_active', true)->get()
            ->filter(fn (AdminUser $admin): bool => $admin->can('licenses.view'));

        foreach ($instances as $instance) {
            $instance->update(['status' => 'abnormal', 'abnormal_notified_at' => now()]);
            if ($admins->isNotEmpty()) {
                Notification::make()
                    ->title('授权心跳异常')
                    ->body("授权 {$instance->licensePackage->license_id} 的实例 {$instance->instance_id} 已超过 {$days} 天未验证。")
                    ->danger()
                    ->sendToDatabase($admins);
            }
        }

        $unverifiedLicenses = LicensePackage::query()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->where('not_before', '<', now()->subDays($days))
            ->whereNull('last_verified_at')
            ->whereNull('heartbeat_abnormal_notified_at')
            ->doesntHave('instances')
            ->get();

        foreach ($unverifiedLicenses as $license) {
            $license->forceFill(['heartbeat_abnormal_notified_at' => now()])->saveQuietly();
            if ($admins->isNotEmpty()) {
                Notification::make()
                    ->title('授权从未上报心跳')
                    ->body("授权 {$license->license_id} 生效已超过 {$days} 天，仍未收到任何在线验证请求。")
                    ->danger()
                    ->sendToDatabase($admins);
            }
        }

        $this->info("检测完成：{$instances->count()} 个异常实例，{$unverifiedLicenses->count()} 个从未验证的授权。");

        return self::SUCCESS;
    }
}
