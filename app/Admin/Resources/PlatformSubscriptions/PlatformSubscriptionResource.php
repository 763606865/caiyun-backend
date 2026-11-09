<?php

namespace App\Admin\Resources\PlatformSubscriptions;

use App\Models\PlatformSubscription;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatformSubscriptionResource extends Resource
{
    protected static ?string $model = PlatformSubscription::class;

    protected static ?string $modelLabel = '企业应用套餐';

    protected static ?string $navigationLabel = '企业应用套餐';

    protected static string|\UnitEnum|null $navigationGroup = '中台管理';

    public static function canAccess(): bool
    {
        return false;
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
                Select::make('application_id')
                    ->relationship('application', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('应用'),
                Select::make('enterprise_id')
                    ->relationship('enterprise', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('企业'),
                TextInput::make('package_name')
                    ->required()
                    ->maxLength(255)
                    ->label('套餐名称'),
                TextInput::make('seat_limit')
                    ->required()
                    ->maxLength(255)
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->label('用户额度'),
                DateTimePicker::make('starts_at')
                    ->required()
                    ->label('开始时间'),
                DateTimePicker::make('ends_at')
                    ->required()
                    ->after('starts_at')
                    ->label('结束时间'),
                Toggle::make('is_enabled')
                    ->default(true)
                    ->label('启用'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.name')
                    ->label('应用'),
                TextColumn::make('enterprise.name')
                    ->label('企业'),
                TextColumn::make('package_name')
                    ->searchable()
                    ->label('套餐名称'),
                TextColumn::make('seat_limit')
                    ->searchable()
                    ->label('用户额度'),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->label('开始时间'),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->label('结束时间'),
                IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('启用'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlatformSubscriptions::route('/'),
        ];
    }
}
