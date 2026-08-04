<?php

namespace App\Admin\Resources\SystemSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SystemSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->visibility('public'),
                TextColumn::make('site_name')
                    ->label('站点名称')
                    ->searchable(),
                TextColumn::make('customer_service_phone')
                    ->label('客服电话')
                    ->placeholder('未设置'),
                TextColumn::make('icp_number')
                    ->label('ICP 备案号')
                    ->placeholder('未设置'),
                IconColumn::make('sms_enabled')
                    ->label('短信')
                    ->boolean(),
                IconColumn::make('registration_enabled')
                    ->label('注册')
                    ->boolean(),
                IconColumn::make('payment_enabled')
                    ->label('支付')
                    ->boolean(),
                TextColumn::make('upload_max_size_mb')
                    ->label('上传限制')
                    ->suffix(' MB'),
                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
