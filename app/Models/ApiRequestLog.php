<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * API 请求日志，不记录请求体、Token 和 Cookie。
 *
 * @property int $id
 * @property string $request_id
 * @property int|null $user_id
 * @property int|null $user_device_id
 * @property string $method
 * @property string $path
 * @property string|null $route_name
 * @property int $status_code
 * @property int $duration_ms
 * @property string|null $ip
 * @property Carbon $requested_at
 */
#[Fillable([
    'request_id', 'user_id', 'user_device_id', 'method', 'path', 'route_name',
    'status_code', 'duration_ms', 'ip', 'requested_at',
])]
class ApiRequestLog extends Model
{
    public $timestamps = false;

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
