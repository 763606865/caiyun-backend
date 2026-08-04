<?php

namespace App\Admin\Resources\ClientVersions\Tables;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('client_type')
                    ->label('客户端')
                    ->badge()
                    ->formatStateUsing(fn (ClientType $state): string => match ($state) {
                        ClientType::Web => '网页',
                        ClientType::WechatMini => '微信小程序',
                        ClientType::AppIos => 'iOS App',
                        ClientType::AppAndroid => 'Android App',
                    })
                    ->sortable(),
                TextColumn::make('channel')
                    ->label('渠道')
                    ->searchable(),
                TextColumn::make('app_version')
                    ->label('版本号')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('app_build')
                    ->label('构建号')
                    ->searchable(),
                IconColumn::make('is_force_update')
                    ->label('强制更新')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->label('已发布')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('发布时间')
                    ->dateTime()
                    ->placeholder('未设置')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('client_type')
                    ->label('客户端类型')
                    ->options([
                        ClientType::Web->value => '网页',
                        ClientType::WechatMini->value => '微信小程序',
                        ClientType::AppIos->value => 'iOS App',
                        ClientType::AppAndroid->value => 'Android App',
                    ]),
                SelectFilter::make('platform')
                    ->label('运行平台')
                    ->options([
                        ClientPlatform::Ios->value => 'iOS',
                        ClientPlatform::Android->value => 'Android',
                        ClientPlatform::Wechat->value => '微信',
                        ClientPlatform::Web->value => '网页',
                    ]),
                SelectFilter::make('is_published')
                    ->label('发布状态')
                    ->options([
                        '1' => '已发布',
                        '0' => '草稿',
                    ]),
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
