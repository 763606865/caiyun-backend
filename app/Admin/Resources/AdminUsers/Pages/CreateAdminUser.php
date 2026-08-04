<?php

namespace App\Admin\Resources\AdminUsers\Pages;

use App\Admin\Resources\AdminUsers\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;
}
