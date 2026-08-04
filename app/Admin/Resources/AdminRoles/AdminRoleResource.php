<?php

namespace App\Admin\Resources\AdminRoles;

use App\Admin\Resources\AdminRoles\Pages\CreateAdminRole;
use App\Admin\Resources\AdminRoles\Pages\EditAdminRole;
use App\Admin\Resources\AdminRoles\Pages\ListAdminRoles;
use App\Admin\Resources\AdminRoles\Schemas\AdminRoleForm;
use App\Admin\Resources\AdminRoles\Tables\AdminRolesTable;
use App\Models\AdminRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminRoleResource extends Resource
{
    protected static ?string $model = AdminRole::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = '角色管理';

    protected static ?string $modelLabel = '角色';

    protected static ?string $pluralModelLabel = '角色';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdminRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminRolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminRoles::route('/'),
            'create' => CreateAdminRole::route('/create'),
            'edit' => EditAdminRole::route('/{record}/edit'),
        ];
    }
}
