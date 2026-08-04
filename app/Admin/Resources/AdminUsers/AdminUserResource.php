<?php

namespace App\Admin\Resources\AdminUsers;

use App\Admin\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Admin\Resources\AdminUsers\Pages\EditAdminUser;
use App\Admin\Resources\AdminUsers\Pages\ListAdminUsers;
use App\Admin\Resources\AdminUsers\Schemas\AdminUserForm;
use App\Admin\Resources\AdminUsers\Tables\AdminUsersTable;
use App\Models\AdminUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = '管理员管理';

    protected static ?string $modelLabel = '管理员';

    protected static ?string $pluralModelLabel = '管理员';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdminUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminUsersTable::configure($table);
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
            'index' => ListAdminUsers::route('/'),
            'create' => CreateAdminUser::route('/create'),
            'edit' => EditAdminUser::route('/{record}/edit'),
        ];
    }
}
