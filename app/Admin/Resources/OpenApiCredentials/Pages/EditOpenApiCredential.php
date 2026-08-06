<?php

namespace App\Admin\Resources\OpenApiCredentials\Pages;

use App\Admin\Resources\OpenApiCredentials\OpenApiCredentialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpenApiCredential extends EditRecord
{
    protected static string $resource = OpenApiCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
