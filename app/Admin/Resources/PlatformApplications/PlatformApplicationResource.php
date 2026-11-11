<?php

namespace App\Admin\Resources\PlatformApplications;

use App\Models\PlatformApplication;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatformApplicationResource extends Resource
{
    protected static ?string $model = PlatformApplication::class;

    protected static ?string $modelLabel = '应用注册';

    protected static ?string $navigationLabel = '应用管理';

    protected static string|\UnitEnum|null $navigationGroup = '中台管理';

    public static function canAccess(): bool
    {
        return auth('admin')->user()?->hasRole('super-admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[A-Za-z0-9_-]+$/')
                    ->unique(ignoreRecord: true)
                    ->label('应用编码'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('应用名称'),
                TextInput::make('base_url')
                    ->maxLength(255)
                    ->url()
                    ->label('应用地址'),
                TextInput::make('app_key')->label('APP_KEY')->disabled()->dehydrated(false)->visibleOn('edit'),
                TextInput::make('app_secret')->label('APP_SECRET')->password()->revealable()->disabled()->dehydrated(false)->visibleOn('edit'),
                TagsInput::make('allowed_ips')->label('IP 白名单')->nestedRecursiveRules(['ip'])->helperText('留空表示不限制'),
                DateTimePicker::make('expires_at')->label('凭证过期时间')->helperText('留空表示长期有效'),
                TextInput::make('webhook_url')->label('Webhook 地址')->url()->maxLength(500),
                Textarea::make('remark')->label('备注'),
                Toggle::make('is_enabled')
                    ->default(true)
                    ->label('启用'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->label('应用编码'),
                TextColumn::make('name')
                    ->searchable()
                    ->label('应用名称'),
                TextColumn::make('app_key')->label('APP_KEY')->copyable(),
                TextColumn::make('base_url')
                    ->searchable()
                    ->label('应用地址'),
                IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('启用'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('rotateSecret')
                    ->label('重置密钥')
                    ->requiresConfirmation()
                    ->modalDescription('重置后旧密钥立即失效。新密钥将在成功提示中显示，请及时复制保存。')
                    ->action(function (PlatformApplication $record): void {
                        $secret = bin2hex(random_bytes(32));
                        $record->app_secret = $secret;
                        $record->save();

                        Notification::make()
                            ->title('密钥已重置，旧密钥立即失效')
                            ->body('APP_SECRET：'.$secret.' 请立即复制保存，关闭提示后不再显示。')
                            ->success()
                            ->persistent()
                            ->actions([
                                Action::make('copySecret')
                                    ->label('复制密钥')
                                    ->alpineClickHandler('navigator.clipboard.writeText('.json_encode($secret).')'),
                            ])
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlatformApplications::route('/'),
        ];
    }
}
