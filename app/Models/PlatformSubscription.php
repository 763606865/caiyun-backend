<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 企业应用套餐表。
 *
 * @property int $id 主键
 * @property int $application_id 所属应用 ID
 * @property int $enterprise_id 所属企业 ID
 * @property string $package_name 套餐名称
 * @property int $seat_limit 用户额度
 * @property Carbon $starts_at 套餐开始时间
 * @property Carbon $ends_at 套餐结束时间
 * @property bool $is_enabled 是否启用
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read PlatformApplication $application 所属应用
 * @property-read PlatformEnterprise $enterprise 所属企业
 */
#[Table(name: 'platform_subscriptions')]
#[Fillable([
    'application_id',
    'enterprise_id',
    'package_name',
    'seat_limit',
    'starts_at',
    'ends_at',
    'is_enabled',
])]
class PlatformSubscription extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<PlatformApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PlatformApplication::class);
    }

    /** @return BelongsTo<PlatformEnterprise, $this> */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(PlatformEnterprise::class);
    }
}
