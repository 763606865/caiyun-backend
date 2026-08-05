<?php

namespace App\Models;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 用户客户端设备表。
 *
 * @property int $id 设备记录主键
 * @property int|null $user_id 最近使用该设备的用户 ID
 * @property string $device_id 客户端安装实例 ID
 * @property ClientType $client_type 客户端类型
 * @property string $app_version 客户端版本号
 * @property string $app_build 客户端构建号
 * @property ClientPlatform $platform 运行平台
 * @property string $os_version 操作系统版本
 * @property string $channel 安装或发布渠道
 * @property string|null $last_ip 最近请求 IP
 * @property string|null $user_agent 最近请求 User-Agent
 * @property Carbon $first_seen_at 首次请求时间
 * @property Carbon $last_seen_at 最近请求时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read User|null $user 最近使用该设备的用户
 * @property-read Collection<int, ApiRequestLog> $requestLogs API 请求日志
 */
#[Table(name: 'user_devices')]
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
