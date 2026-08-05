<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * API 请求日志表，不记录请求体、Token 和 Cookie。
 *
 * @property int $id 日志主键
 * @property string $request_id 请求链路 ID
 * @property int|null $user_id 请求用户 ID
 * @property int|null $user_device_id 请求设备记录 ID
 * @property string $method HTTP 请求方法
 * @property string $path 不含查询参数的请求路径
 * @property string|null $route_name Laravel 路由名称
 * @property int $status_code HTTP 响应状态码
 * @property int $duration_ms 服务端处理耗时（毫秒）
 * @property string|null $ip 请求 IP
 * @property Carbon $requested_at 请求时间
 * @property-read User|null $user 请求用户
 * @property-read UserDevice|null $userDevice 请求设备
 */
#[Table(name: 'api_request_logs', timestamps: false)]
#[Fillable([
    'request_id', 'user_id', 'user_device_id', 'method', 'path', 'route_name',
    'status_code', 'duration_ms', 'ip', 'requested_at',
])]
class ApiRequestLog extends Model
{
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<UserDevice, $this> */
    public function userDevice(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'duration_ms' => 'integer',
            'requested_at' => 'datetime',
        ];
    }
}
