<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

/**
 * 后台角色表。
 *
 * @property int $id 角色主键
 * @property string $name 角色标识
 * @property string $guard_name 认证守卫名称
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'roles')]
class AdminRole extends Role
{
    protected $attributes = [
        'guard_name' => 'admin',
    ];

    public function label(): string
    {
        return $this->name === 'super-admin' ? '超级管理员' : $this->name;
    }
}
