<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 用户绑定的第三方平台账号。
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $app_id
 * @property string $provider_account_id
 * @property string|null $union_id
 * @property string|null $mobile
 * @property string|null $nickname
 * @property string|null $avatar_url
 * @property array<string, mixed>|null $data
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
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
