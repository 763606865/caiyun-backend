<?php

namespace App\Admin\Resources\OpenApiCredentials\Pages;

use App\Admin\Resources\OpenApiCredentials\OpenApiCredentialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpenApiCredentials extends ListRecords
{
    protected static string $resource = OpenApiCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
