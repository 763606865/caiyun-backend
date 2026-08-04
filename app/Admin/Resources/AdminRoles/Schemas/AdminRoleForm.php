<?php

namespace App\Admin\Resources\AdminRoles\Schemas;

use App\Models\AdminPermission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdminRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('角色名称')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('guard_name', 'admin'))
                    ->maxLength(255),
                Hidden::make('guard_name')
                    ->default('admin'),
                CheckboxList::make('permissions')
                    ->label('权限')
                    ->relationship(
                        name: 'permissions',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('guard_name', 'admin'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (AdminPermission $record): string => $record->label(),
                    )
                    ->bulkToggleable()
                    ->columns(2)
                    ->searchable(),
            ]);
    }
}
