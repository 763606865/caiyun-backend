<?php

namespace App\Admin\Resources\OpenApiCredentials\Pages;

use App\Admin\Resources\OpenApiCredentials\OpenApiCredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpenApiCredential extends CreateRecord
{
    protected static string $resource = OpenApiCredentialResource::class;
}
