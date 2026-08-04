<?php

namespace App\Admin\Resources\SystemSettings\Pages;

use App\Admin\Resources\SystemSettings\SystemSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingResource::class;
}
