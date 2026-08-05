<?php

namespace App\Admin\Resources\OperationLogs;

use App\Admin\Resources\OperationLogs\Pages\ListOperationLogs;
use App\Models\OperationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OperationLogResource extends Resource
{
    protected static ?string $model = OperationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = '操作日志';

    protected static ?string $modelLabel = '操作日志';

    protected static string|UnitEnum|null $navigationGroup = '系统管理';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([TextColumn::make('adminUser.name')->label('管理员'), TextColumn::make('action')->label('操作')->badge(), TextColumn::make('subject_type')->label('对象'), TextColumn::make('subject_id')->label('ID'), TextColumn::make('ip')->label('IP'), TextColumn::make('created_at')->label('时间')->dateTime()]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ListOperationLogs::route('/')];
    }
}
