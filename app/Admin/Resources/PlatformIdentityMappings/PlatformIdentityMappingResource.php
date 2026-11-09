<?php

namespace App\Admin\Resources\PlatformIdentityMappings;

use App\Models\PlatformIdentityMapping;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatformIdentityMappingResource extends Resource
{
    protected static ?string $model = PlatformIdentityMapping::class;

    protected static ?string $modelLabel = '同步映射';

    protected static ?string $navigationLabel = '同步映射';

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
                Select::make('entity_type')
                    ->options(['enterprise' => '企业', 'organization' => '组织', 'user' => '用户',
                    ])
                    ->required()->label('同步类型'), TextInput::make('external_id')->required()->maxLength(255)->label('应用内 ID'), TextInput::make('canonical_id')->required()->maxLength(255)->label('中台记录 ID')]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.name')
                    ->label('应用'),
                TextColumn::make('enterprise.name')
                    ->label('企业'),
                TextColumn::make('entity_type')
                    ->label('同步类型'),
                TextColumn::make('external_id')
                    ->searchable()
                    ->label('应用内 ID'),
                TextColumn::make('canonical_id')
                    ->searchable()
                    ->label('中台记录 ID'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlatformIdentityMappings::route('/'),
        ];
    }
}
