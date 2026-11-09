<?php

namespace App\Admin\Resources\Users\Tables;

use App\Enums\UserStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->searchable(),
                TextColumn::make('uuid')->label('UUID')->copyable(),
                TextColumn::make('source')->label('用户来源')->searchable(),
                TextColumn::make('status')->label('状态')->formatStateUsing(fn (UserStatus $state) => $state->label()),
                TextColumn::make('last_login_at')->label('最后登录时间')->dateTime(),
                TextColumn::make('nick_name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('手机号')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('邮箱验证时间')
                    ->dateTime()
                    ->placeholder('未验证')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('状态')->options([0 => '禁用', 1 => '正常']),
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
