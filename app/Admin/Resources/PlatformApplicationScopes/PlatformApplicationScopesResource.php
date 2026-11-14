<?php

namespace App\Admin\Resources\PlatformApplicationScopes;

use App\Models\PlatformApplicationScope;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** 只读接入管理；所有写入由独立的受控人工绑定动作执行。 */
class PlatformApplicationScopesResource extends Resource
{
    protected static ?string $model = PlatformApplicationScope::class;

    protected static ?string $modelLabel = '应用接口范围';

    protected static ?string $navigationLabel = '应用接口范围';

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
            TextColumn::make('application.code')->label('应用')->searchable()->sortable(),
            TextColumn::make('enterprise.name')->label('企业（空表示显式全局）')->searchable()->sortable(),
            TextColumn::make('scope')->label('接口权限')->searchable()->sortable(),
        ])->defaultSort('id', 'desc')->recordActions([])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePlatformApplicationScopes::route('/')];
    }
}
