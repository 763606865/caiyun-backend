<?php

namespace App\Admin\Resources\AdminRoles\Pages;

use App\Admin\Resources\AdminRoles\AdminRoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminRole extends CreateRecord
{
    protected static string $resource = AdminRoleResource::class;
}
