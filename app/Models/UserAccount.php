<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 用户第三方平台账号表。
 *
 * @property int $id 账号记录主键
 * @property int $user_id 所属用户 ID
 * @property string $provider 第三方平台标识
 * @property string $app_id 第三方应用 ID
 * @property string $provider_account_id 平台账号唯一标识
 * @property string|null $union_id 平台跨应用统一标识
 * @property string|null $mobile 第三方平台认证的手机号
 * @property string|null $nickname 第三方平台昵称
 * @property string|null $avatar_url 第三方平台头像地址
 * @property array<string, mixed>|null $data 平台扩展信息
 * @property Carbon|null $last_login_at 最近一次通过该账号登录时间
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read User $user 所属站内用户
 */
#[Table(name: 'user_accounts')]
#[Fillable([
    'user_id',
    'provider',
    'app_id',
    'provider_account_id',
    'union_id',
    'mobile',
    'nickname',
    'avatar_url',
    'data',
    'last_login_at',
])]
class UserAccount extends Model
{
    public const PROVIDER_WECHAT = 'wechat';

    /**
     * 第三方账号所属的站内用户。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 统一清理第三方平台返回的手机号格式。
     *
     * @return Attribute<string|null, string|null>
     */
    protected function mobile(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value): ?string => $value === null
                ? null
                : preg_replace('/\D+/', '', $value),
        );
    }

    /**
     * 模型字段类型转换。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'last_login_at' => 'datetime',
        ];
    }
}
