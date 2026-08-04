<?php

namespace App\Models;

use Spatie\Permission\Models\Role;

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
