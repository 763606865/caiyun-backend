<?php

namespace App\Models;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use App\Support\ClientVersionChecker;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 客户端版本及升级策略表。
 *
 * @property int $id 版本记录主键
 * @property ClientType $client_type 客户端类型
 * @property ClientPlatform $platform 运行平台
 * @property string $channel 安装或发布渠道
 * @property string $app_version 对用户展示的客户端版本号
 * @property string $app_build 客户端构建号
 * @property string|null $min_supported_version 最低支持的客户端版本号
 * @property string|null $min_supported_build 最低支持的客户端构建号
 * @property bool $is_force_update 是否强制更新
 * @property bool $is_published 是否已发布
 * @property string|null $download_url 客户端下载或应用商店地址
 * @property string|null $release_notes 版本更新说明
 * @property Carbon|null $published_at 发布时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 *
 * @method static Builder<static> published() 只查询当前已发布的客户端版本
 */
#[Table(name: 'client_versions')]
#[Fillable([
    'client_type', 'platform', 'channel', 'app_version', 'app_build',
    'min_supported_version', 'min_supported_build', 'is_force_update',
    'is_published', 'download_url', 'release_notes', 'published_at',
])]
class ClientVersion extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'channel' => 'official',
        'is_force_update' => false,
        'is_published' => false,
    ];

    /**
     * 只查询已发布的客户端版本。
     *
     * @param  Builder<ClientVersion>  $query
     * @return Builder<ClientVersion>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected static function booted(): void
    {
        static::saved(function (ClientVersion $version): void {
            $version->forgetVersionPolicyCache();
            $version->forgetOriginalVersionPolicyCache();
        });

        static::deleted(function (ClientVersion $version): void {
            $version->forgetVersionPolicyCache();
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'client_type' => ClientType::class,
            'platform' => ClientPlatform::class,
            'is_force_update' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    private function forgetVersionPolicyCache(): void
    {
        app(ClientVersionChecker::class)->forget(
            $this->client_type->value,
            $this->platform->value,
            $this->channel,
        );
    }

    private function forgetOriginalVersionPolicyCache(): void
    {
        $clientType = $this->getRawOriginal('client_type');
        $platform = $this->getRawOriginal('platform');
        $channel = $this->getRawOriginal('channel');

        if (! is_string($clientType) || ! is_string($platform) || ! is_string($channel)) {
            return;
        }

        app(ClientVersionChecker::class)->forget($clientType, $platform, $channel);
    }
}
