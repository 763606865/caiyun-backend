<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 中台应用注册表。
 *
 * @property string $app_key 应用公开凭证
 * @property string $app_secret 加密存储的应用密钥
 * @property array|null $allowed_ips IP 白名单
 * @property Carbon|null $expires_at 凭证过期时间
 * @property string|null $webhook_url 事件回调地址
 * @property string|null $remark 备注
 * @property int $id 主键
 * @property string $code 唯一编码
 * @property string $name 名称
 * @property string $base_url 应用访问地址
 * @property bool $is_enabled 是否启用
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'platform_applications')]
#[Fillable([
    'code',
    'name',
    'base_url',
    'is_enabled',
    'allowed_ips', 'expires_at', 'webhook_url', 'remark',
])]
class PlatformApplication extends Model
{
    protected $hidden = ['app_secret'];

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            $application->app_key = bin2hex(random_bytes(16));
            $application->app_secret = bin2hex(random_bytes(32));
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'app_secret' => 'encrypted',
            'allowed_ips' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
