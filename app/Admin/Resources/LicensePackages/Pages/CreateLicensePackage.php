<?php

namespace App\Admin\Resources\LicensePackages\Pages;

use App\Admin\Resources\LicensePackages\LicensePackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLicensePackage extends CreateRecord
{
    protected static string $resource = LicensePackageResource::class;
}
