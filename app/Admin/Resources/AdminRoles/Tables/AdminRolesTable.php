<?php

namespace App\Admin\Resources\AdminRoles\Tables;

use App\Models\AdminRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminRolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('角色名称')
                    ->formatStateUsing(
                        fn (string $state, AdminRole $record): string => $record->label(),
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('权限数')
                    ->counts('permissions')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('管理员数')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
