<?php

namespace App\Admin\Resources\LicensePackages\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstancesRelationManager extends RelationManager
{
    protected static string $relationship = 'instances';

    protected static ?string $title = '授权实例';

    public function table(Table $table): Table
    {
        return $table->defaultSort('last_verified_at', 'desc')->columns([
            TextColumn::make('instance_id')->label('实例 ID')->copyable()->searchable(),
            TextColumn::make('domain')->label('域名')->searchable(),
            TextColumn::make('project')->label('项目')->placeholder('—'),
            TextColumn::make('app_version')->label('版本')->placeholder('—'),
            TextColumn::make('status')->label('心跳状态')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'active' ? '正常' : '异常')
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),
            TextColumn::make('last_verified_at')->label('最后验证')->dateTime()->sortable(),
            TextColumn::make('last_ip')->label('最后 IP')->placeholder('—'),
        ]);
    }
}
