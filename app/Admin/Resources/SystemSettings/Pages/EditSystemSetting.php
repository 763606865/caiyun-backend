<?php

namespace App\Admin\Resources\SystemSettings\Pages;

use App\Admin\Resources\SystemSettings\SystemSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditSystemSetting extends EditRecord
{
    protected static string $resource = SystemSettingResource::class;
}
