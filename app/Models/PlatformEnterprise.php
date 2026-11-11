<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * 中台企业表。
 *
 * @property string|null $short_name 简称
 * @property string|null $credit_code 统一社会信用代码
 * @property string|null $contact_name 联系人
 * @property string|null $contact_mobile 联系电话
 * @property string|null $address 企业地址
 * @property string|null $description 企业简介
 * @property string|null $remark 备注
 * @property string $uuid 全局唯一标识（UUID v7）
 * @property int $id 主键
 * @property string $code 唯一编码
 * @property string $name 名称
 * @property bool $is_enabled 是否启用
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'platform_enterprises')]
#[Fillable([
    'short_name',
    'credit_code',
    'contact_name',
    'contact_mobile',
    'address',
    'description',
    'remark',
    'admin_user_id',

    'code',
    'name',
    'is_enabled',
])]
class PlatformEnterprise extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $record): void {
            $record->uuid ??= (string) Str::uuid7();
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    /** @return BelongsTo<PlatformOrganization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(PlatformOrganization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /** @return HasMany<PlatformEmployee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(PlatformEmployee::class, 'enterprise_id');
    }

    /** @return HasMany<PlatformSubscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(PlatformSubscription::class, 'enterprise_id');
    }
}
