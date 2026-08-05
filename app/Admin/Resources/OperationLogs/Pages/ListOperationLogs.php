<?php

namespace App\Admin\Resources\OperationLogs\Pages;

use App\Admin\Resources\OperationLogs\OperationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListOperationLogs extends ListRecords
{
    protected static string $resource = OperationLogResource::class;
}
