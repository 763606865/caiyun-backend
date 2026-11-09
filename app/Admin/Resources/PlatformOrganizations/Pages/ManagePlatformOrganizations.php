<?php

namespace App\Admin\Resources\PlatformOrganizations\Pages;

use App\Admin\Resources\PlatformOrganizations\PlatformOrganizationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformOrganizations extends ManageRecords
{
    protected static string $resource = PlatformOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
