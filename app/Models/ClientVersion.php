<?php

namespace App\Models;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use App\Support\ClientVersionChecker;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 客户端发布版本及升级策略。
 *
 * @property int $id
 * @property ClientType $client_type
 * @property ClientPlatform $platform
 * @property string $channel
 * @property string $app_version
 * @property string $app_build
 * @property string|null $min_supported_version
 * @property string|null $min_supported_build
 * @property bool $is_force_update
 * @property bool $is_published
 * @property string|null $download_url
 * @property string|null $release_notes
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
    public function scopePublished(Builder $query): Builder
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
