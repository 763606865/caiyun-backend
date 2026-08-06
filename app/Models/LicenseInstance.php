<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 授权部署实例及其最新心跳状态。
 *
 * @property int $id 实例主键
 * @property int $license_package_id 授权包主键
 * @property int|null $open_api_credential_id 最近使用的 OpenAPI 凭据主键
 * @property string $instance_id 客户端持久化 UUID
 * @property string $domain 部署域名
 * @property string|null $project 项目标识
 * @property string|null $app_version 应用版本
 * @property string $status active/abnormal
 * @property Carbon $first_verified_at 首次验证时间
 * @property Carbon $last_verified_at 最后成功验证时间
 * @property string|null $last_ip 最后请求 IP
 * @property Carbon|null $abnormal_notified_at 异常通知时间
 */
#[Table(name: 'license_instances')]
#[Fillable([
    'license_package_id', 'open_api_credential_id', 'instance_id', 'domain', 'project',
    'app_version', 'status', 'first_verified_at', 'last_verified_at', 'last_ip', 'abnormal_notified_at',
])]
class LicenseInstance extends Model
{
    /** @return BelongsTo<LicensePackage, $this> */
    public function licensePackage(): BelongsTo
    {
        return $this->belongsTo(LicensePackage::class);
    }

    /** @return BelongsTo<OpenApiCredential, $this> */
    public function openApiCredential(): BelongsTo
    {
        return $this->belongsTo(OpenApiCredential::class);
    }

    /** @return HasMany<LicenseVerificationLog, $this> */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(LicenseVerificationLog::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'first_verified_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'abnormal_notified_at' => 'datetime',
        ];
    }
}
