<?php

namespace App\Admin\Resources\LicensePackages\Pages;

use App\Admin\Resources\LicensePackages\LicensePackageResource;
use App\Models\OpenApiCredential;
use Filament\Resources\Pages\CreateRecord;

class CreateLicensePackage extends CreateRecord
{
    protected static string $resource = LicensePackageResource::class;

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['open_api_credential_id'])) {
            $credential = OpenApiCredential::query()->create([
                'name' => '授权-'.($data['customer'] ?? '未命名'),
            ]);
            $data['open_api_credential_id'] = $credential->id;
        }

        return $data;
    }
}
