<?php

namespace App\Admin\Resources\Tags\Pages;

use App\Admin\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
