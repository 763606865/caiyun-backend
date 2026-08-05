<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * 后台管理员表。
 *
 * @property int $id 管理员主键
 * @property string $name 管理员姓名
 * @property string $email 登录邮箱
 * @property Carbon|null $email_verified_at 邮箱验证时间
 * @property string $password 登录密码哈希
 * @property bool $is_active 是否启用账号
 * @property Carbon|null $last_login_at 最近登录时间
 * @property string|null $last_login_ip 最近登录 IP
 * @property string|null $remember_token 记住登录令牌
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'admin_users')]
#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class AdminUser extends Authenticatable implements FilamentUser
{
    use HasRoles, Notifiable;

    protected string $guard_name = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_active;
    }
}
