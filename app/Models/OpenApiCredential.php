<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * OpenAPI 调用凭据表。
 *
 * @property int $id 凭据主键
 * @property string $name 调用方名称
 * @property string $client_id 对外公开的客户端标识
 * @property string $client_secret 加密存储的客户端密钥
 * @property list<string>|null $allowed_ips 允许调用的 IP 或 CIDR 列表，空表示不限制
 * @property bool $is_active 是否启用
 * @property Carbon|null $expires_at 凭据过期时间，空表示永不过期
 * @property Carbon|null $last_used_at 最后成功调用时间
 * @property string|null $last_used_ip 最后成功调用 IP
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'open_api_credentials')]
#[Fillable([
    'name', 'client_id', 'client_secret', 'allowed_ips', 'is_active', 'expires_at',
])]
class OpenApiCredential extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'is_active' => true,
    ];

    public static function generateSecret(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /** @return HasMany<LicensePackage, $this> */
    public function licensePackages(): HasMany
    {
        return $this->hasMany(LicensePackage::class);
    }

    protected static function booted(): void
    {
        static::creating(function (OpenApiCredential $credential): void {
            if ($credential->client_id === null || $credential->client_id === '') {
                $credential->client_id = 'opk_'.Str::lower(Str::random(32));
            }
            if ($credential->client_secret === null || $credential->client_secret === '') {
                $credential->client_secret = self::generateSecret();
            }
        });

        // 轮换 Client ID/Secret 后，绑定的授权书需重新签发以便下载到新凭据。
        static::updated(function (OpenApiCredential $credential): void {
            if (! $credential->wasChanged(['client_id', 'client_secret'])) {
                return;
            }

            $credential->licensePackages()->each(function (LicensePackage $license): void {
                $license->resignCertificate();
            });
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'allowed_ips' => 'array',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }
}
