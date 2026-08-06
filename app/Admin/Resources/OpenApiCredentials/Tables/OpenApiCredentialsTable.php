<?php

namespace App\Admin\Resources\OpenApiCredentials\Tables;

use App\Models\OpenApiCredential;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OpenApiCredentialsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('name')->label('调用方')->searchable(),
            TextColumn::make('client_id')->label('Client ID')->searchable()->copyable(),
            IconColumn::make('is_active')->label('启用')->boolean(),
            TextColumn::make('expires_at')->label('过期时间')->dateTime()->placeholder('永不过期')->sortable(),
            TextColumn::make('last_used_at')->label('最后调用')->dateTime()->placeholder('从未')->sortable(),
            TextColumn::make('last_used_ip')->label('最后 IP')->placeholder('—'),
        ])->recordActions([
            Action::make('rotate')->label('轮换密钥')->icon('heroicon-o-arrow-path')->color('warning')
                ->visible(fn (): bool => (bool) auth('admin')->user()?->can('openapi-credentials.rotate'))
                ->requiresConfirmation()->schema([
                    TextInput::make('client_secret')->label('新 Client Secret')->password()->revealable()->required()
                        ->default(fn (): string => OpenApiCredential::generateSecret()),
                ])->action(function (OpenApiCredential $record, array $data): void {
                    $record->update(['client_secret' => $data['client_secret']]);
                    Notification::make()->title('密钥已轮换，旧密钥立即失效')->success()->send();
                }),
            EditAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }
}
