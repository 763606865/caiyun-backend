<?php

namespace App\Admin\Resources\PlatformIdentityMappings\Pages;

use App\Admin\Resources\PlatformIdentityMappings\PlatformIdentityMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformIdentityMappings extends ManageRecords
{
    protected static string $resource = PlatformIdentityMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
