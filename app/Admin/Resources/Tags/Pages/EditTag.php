<?php

namespace App\Admin\Resources\Tags\Pages;

use App\Admin\Resources\Tags\TagResource;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;
}
