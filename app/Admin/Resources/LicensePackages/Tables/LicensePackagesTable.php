<?php

namespace App\Admin\Resources\LicensePackages\Tables;

use App\Models\LicensePackage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LicensePackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('license_id')->label('授权编号')->searchable()->copyable(),
                TextColumn::make('customer')->label('客户')->searchable(),
                TextColumn::make('project')->label('项目')->placeholder('不限')->searchable(),
                TextColumn::make('plan')->label('套餐')->badge(),
                TextColumn::make('status')->label('状态')->state(fn (LicensePackage $record): string => $record->status())
                    ->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => '有效', 'pending' => '未生效', 'expired' => '已过期', 'revoked' => '已撤销',
                        default => $state,
                    })->color(fn (string $state): string => match ($state) {
                        'active' => 'success', 'pending' => 'warning', 'expired' => 'gray', 'revoked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('expires_at')->label('到期时间')->dateTime()->sortable(),
                TextColumn::make('last_verified_at')->label('最后在线校验')->dateTime()->placeholder('从未')->toggleable(),
                TextColumn::make('heartbeat_status')->label('心跳状态')
                    ->state(fn (LicensePackage $record): string => $record->heartbeatStatus())
                    ->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal' => '正常', 'pending' => '等待首次验证', 'never_verified' => '从未验证',
                        'abnormal' => '超过 7 天未验证', 'inactive' => '已过期/撤销', default => $state,
                    })->color(fn (string $state): string => match ($state) {
                        'normal' => 'success', 'pending' => 'warning', 'never_verified', 'abnormal' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('instances_count')->counts('instances')->label('部署实例')->badge(),
            ])
            ->filters([
                SelectFilter::make('project')->label('项目')->options(fn (): array => LicensePackage::query()->whereNotNull('project')->distinct()->pluck('project', 'project')->all()),
            ])
            ->recordActions([
                Action::make('download')->label('下载')->icon('heroicon-o-arrow-down-tray')
                    ->authorize('view')->action(fn (LicensePackage $record) => response()->streamDownload(
                        fn () => print ($record->token), $record->license_id.'.license', ['Content-Type' => 'text/plain; charset=UTF-8']
                    )),
                Action::make('revoke')->label('撤销')->icon('heroicon-o-no-symbol')->color('danger')
                    ->visible(fn (LicensePackage $record): bool => $record->revoked_at === null && auth('admin')->user()?->can('licenses.revoke'))
                    ->requiresConfirmation()->schema([
                        Textarea::make('reason')->label('撤销原因')->required()->maxLength(255),
                    ])->action(function (LicensePackage $record, array $data): void {
                        $record->forceFill(['revoked_at' => now(), 'revoked_reason' => $data['reason']])->saveQuietly();
                        Notification::make()->title('授权已撤销')->success()->send();
                    }),
                Action::make('restore')->label('恢复')->color('success')
                    ->visible(fn (LicensePackage $record): bool => $record->revoked_at !== null && auth('admin')->user()?->can('licenses.revoke'))
                    ->requiresConfirmation()->action(function (LicensePackage $record): void {
                        $record->forceFill(['revoked_at' => null, 'revoked_reason' => null])->saveQuietly();
                        Notification::make()->title('授权已恢复')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }
}
