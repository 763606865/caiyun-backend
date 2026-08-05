<?php

namespace App\Admin\Resources\Contents\Pages;

use App\Admin\Resources\Contents\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;
}
