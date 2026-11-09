<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 企业职工，一个用户可以加入多个企业。
 *
 * @property int $id 职工关联 ID
 * @property int $enterprise_id 所属企业 ID
 * @property int $user_id 用户 ID
 * @property string|null $employee_no 企业内职工编号
 * @property string|null $position 职务
 * @property bool $is_enabled 是否在职启用
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read User $user 职工用户
 * @property-read PlatformEnterprise $enterprise 所属企业
 */
#[Table(name: 'platform_employees')]
#[Fillable([
    'enterprise_id',
    'user_id',
    'employee_no',
    'position',
    'is_enabled',
])]
class PlatformEmployee extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PlatformEnterprise, $this> */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(PlatformEnterprise::class);
    }
}
