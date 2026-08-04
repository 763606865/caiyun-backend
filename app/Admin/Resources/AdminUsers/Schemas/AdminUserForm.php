<?php

namespace App\Admin\Resources\AdminUsers\Schemas;

use App\Models\AdminRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('姓名')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8)
                    ->maxLength(255),
                Select::make('roles')
                    ->label('角色')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('guard_name', 'admin'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (AdminRole $record): string => $record->label(),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Toggle::make('is_active')
                    ->label('启用')
                    ->default(true)
                    ->required(),
            ]);
    }
}
