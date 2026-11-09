<?php

namespace App\Admin\Resources\PlatformEnterprises;

use App\Models\PlatformEnterprise;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatformEnterpriseResource extends Resource
{
    protected static ?string $model = PlatformEnterprise::class;

    protected static ?string $modelLabel = '企业管理';

    protected static ?string $navigationLabel = '企业管理';

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
            ->columns(2)
            ->components([
                TextInput::make('short_name')
                    ->label('简称')
                    ->maxLength(255),
                TextInput::make('credit_code')
                    ->label('统一社会信用代码')
                    ->maxLength(255),
                TextInput::make('contact_name')
                    ->label('联系人')
                    ->maxLength(255),
                TextInput::make('contact_mobile')
                    ->label('联系电话')
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('企业地址')
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('企业简介')
                    ->maxLength(255),
                TextInput::make('remark')
                    ->label('备注')
                    ->maxLength(255),
                Select::make('admin_user_id')->label('企业管理员')->relationship('administrator', 'phone')->searchable()->preload()->required(),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[A-Za-z0-9_-]+$/')
                    ->unique(ignoreRecord: true)
                    ->label('企业编码'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('企业名称'),
                Toggle::make('is_enabled')
                    ->default(true)
                    ->label('启用'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('short_name')->label('简称')->searchable()->toggleable(),
                TextColumn::make('credit_code')->label('统一社会信用代码')->searchable()->toggleable(),
                TextColumn::make('contact_name')->label('联系人')->searchable()->toggleable(),
                TextColumn::make('contact_mobile')->label('联系电话')->searchable()->toggleable(),
                TextColumn::make('address')->label('企业地址')->searchable()->toggleable(),
                TextColumn::make('description')->label('企业简介')->searchable()->toggleable(),
                TextColumn::make('remark')->label('备注')->searchable()->toggleable(),

                TextColumn::make('code')
                    ->searchable()
                    ->label('企业编码'),
                TextColumn::make('name')
                    ->searchable()
                    ->label('企业名称'),
                IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('启用'),
            ])
            ->filters([TernaryFilter::make('is_enabled')->label('启用状态')])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlatformEnterprises::route('/'),
            'create' => Pages\CreatePlatformEnterprise::route('/create'),
            'edit' => Pages\EditPlatformEnterprise::route('/{record}/edit'),
        ];
    }
}
