<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

/**
 * 后台权限表。
 *
 * @property int $id 权限主键
 * @property string $name 权限标识
 * @property string $guard_name 认证守卫名称
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'permissions')]
class AdminPermission extends Permission
{
    protected $attributes = [
        'guard_name' => 'admin',
    ];

    public function label(): string
    {
        return [
            'admin-users.view' => '查看管理员',
            'admin-users.create' => '创建管理员',
            'admin-users.update' => '编辑管理员',
            'admin-users.delete' => '删除管理员',
            'roles.view' => '查看角色',
            'roles.create' => '创建角色',
            'roles.update' => '编辑角色',
            'roles.delete' => '删除角色',
            'users.view' => '查看用户',
            'users.create' => '创建用户',
            'users.update' => '编辑用户',
            'users.delete' => '删除用户',
        ][$this->name] ?? $this->name;
    }
}
