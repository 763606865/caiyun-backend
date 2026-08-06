<?php

namespace App\Admin\Resources\LicensePackages\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VerificationLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'verificationLogs';

    protected static ?string $title = '验证日志';

    public function table(Table $table): Table
    {
        return $table->defaultSort('verified_at', 'desc')->columns([
            TextColumn::make('verified_at')->label('验证时间')->dateTime()->sortable(),
            TextColumn::make('instance_id')->label('实例 ID')->copyable()->searchable(),
            TextColumn::make('domain')->label('域名')->searchable(),
            TextColumn::make('app_version')->label('版本')->placeholder('—'),
            TextColumn::make('result')->label('结果')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'success' ? '成功' : '拒绝')
                ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger'),
            TextColumn::make('reason')->label('原因')->placeholder('—'),
            TextColumn::make('ip')->label('请求 IP')->placeholder('—'),
        ])->filters([
            SelectFilter::make('result')->label('验证结果')->options(['success' => '成功', 'rejected' => '拒绝']),
        ]);
    }
}
