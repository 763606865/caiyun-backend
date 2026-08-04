<?php

namespace App\Models;

use App\Enums\UserGender;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * 用户表
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $real_name
 * @property string|null $nick_name
 * @property string|null $phone
 * @property string|null $avatar
 * @property UserGender|null $gender
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property UserStatus $status
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'real_name',
    'nick_name',
    'phone',
    'avatar',
    'gender',
    'email',
    'status',
    'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * 注册模型事件。
     *
     * 新建用户时自动生成 UUID v7；数据库内以 16 字节二进制保存。
     */
    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            /** @var self $model */
            $model->uuid ??= (string) Str::uuid7();
            $model->gender ??= UserGender::Unknown;
            $model->status ??= UserStatus::Normal;
        });
    }

    /**
     * 用户绑定的第三方平台账号。
     *
     * @return HasMany<UserAccount, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(UserAccount::class);
    }

    /**
     * 用户使用过的客户端设备。
     *
     * @return HasMany<UserDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * 在标准 UUID 字符串与数据库二进制格式之间转换。
     *
     * @return Attribute<string|null, string|null>
     */
    protected function uuid(): Attribute
    {
        return Attribute::make(
            get: static function (?string $value): ?string {
                if ($value === null || strlen($value) !== 16) {
                    return $value;
                }

                $hex = bin2hex($value);

                return sprintf(
                    '%s-%s-%s-%s-%s',
                    substr($hex, 0, 8),
                    substr($hex, 8, 4),
                    substr($hex, 12, 4),
                    substr($hex, 16, 4),
                    substr($hex, 20),
                );
            },
            set: static function (?string $value): ?string {
                if ($value === null || strlen($value) === 16) {
                    return $value;
                }

                $hex = str_replace('-', '', $value);

                if (strlen($hex) !== 32 || ! ctype_xdigit($hex)) {
                    return $value;
                }

                return hex2bin($hex) ?: $value;
            },
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'gender' => UserGender::class,
            'status' => UserStatus::class,
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }
}
