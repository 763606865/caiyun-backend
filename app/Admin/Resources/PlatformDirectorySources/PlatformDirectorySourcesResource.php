<?php

namespace App\Admin\Resources\PlatformDirectorySources;

use App\Models\PlatformDirectorySource;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** 只读接入管理；所有写入由独立的受控人工绑定动作执行。 */
class PlatformDirectorySourcesResource extends Resource
{
    protected static ?string $model = PlatformDirectorySource::class;

    protected static ?string $modelLabel = '目录来源绑定';

    protected static ?string $navigationLabel = '目录来源绑定';

    protected static string|\UnitEnum|null $navigationGroup = '中台管理';

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();

        return $admin !== null && $admin->is_active && $admin->hasRole('super-admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('application.code')->label('来源应用')->searchable()->sortable(),
            TextColumn::make('enterprise.name')->label('企业')->searchable()->sortable(),
            TextColumn::make('entity_type')->label('类型')->searchable()->sortable(),
            TextColumn::make('source_id')->label('来源ID')->searchable()->sortable(),
            TextColumn::make('entity_uuid')->label('统一标识')->searchable()->sortable(),
            TextColumn::make('source_version')->label('源版本')->searchable()->sortable(),
        ])->defaultSort('id', 'desc')->recordActions([])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePlatformDirectorySources::route('/')];
    }
}
