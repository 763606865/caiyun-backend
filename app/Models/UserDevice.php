<?php

namespace App\Models;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 客户端安装实例。
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $device_id
 * @property ClientType $client_type
 * @property string $app_version
 * @property string $app_build
 * @property ClientPlatform $platform
 * @property string $os_version
 * @property string $channel
 * @property string|null $last_ip
 * @property string|null $user_agent
 * @property Carbon $first_seen_at
 * @property Carbon $last_seen_at
 * @property-read User|null $user
 */
#[Fillable([
    'user_id', 'device_id', 'client_type', 'app_version', 'app_build', 'platform',
    'os_version', 'channel', 'last_ip', 'user_agent', 'first_seen_at', 'last_seen_at',
])]
class UserDevice extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ApiRequestLog, $this> */
    public function requestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'client_type' => ClientType::class,
            'platform' => ClientPlatform::class,
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}
