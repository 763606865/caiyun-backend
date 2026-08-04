<?php

namespace App\Admin\Resources\LicensePackages\Pages;

use App\Admin\Resources\LicensePackages\LicensePackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLicensePackage extends EditRecord
{
    protected static string $resource = LicensePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
