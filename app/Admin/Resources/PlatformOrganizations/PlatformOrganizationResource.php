<?php

namespace App\Admin\Resources\PlatformOrganizations;

use App\Models\PlatformOrganization;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PlatformOrganizationResource extends Resource
{
    protected static ?string $model = PlatformOrganization::class;

    protected static ?string $modelLabel = '组织管理';

    protected static ?string $navigationLabel = '组织管理';

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
                Select::make('parent_id')->label('上级组织')->relationship('parent', 'name', ignoreRecord: true)->searchable()->preload(),
                TextInput::make('short_name')
                    ->label('简称')
                    ->maxLength(255),
                TextInput::make('primary_type')
                    ->label('主要类型')
                    ->maxLength(255),
                TextInput::make('area_code')
                    ->label('地区码')
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('详细地址')
                    ->maxLength(255),
                TextInput::make('logo')
                    ->label('Logo')
                    ->maxLength(255),
                TextInput::make('website')
                    ->label('官方网站')
                    ->maxLength(255),
                TextInput::make('contact_name')
                    ->label('联系人')
                    ->maxLength(255),
                TextInput::make('contact_mobile')
                    ->label('联系电话')
                    ->maxLength(255),
                TextInput::make('contact_email')
                    ->label('联系邮箱')
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('组织简介')
                    ->maxLength(255),
                TextInput::make('verified_status')
                    ->label('认证状态')
                    ->maxLength(255),
                TextInput::make('remark')
                    ->label('备注')
                    ->maxLength(255),
                Toggle::make('is_enabled')->label('启用')->default(true),
                Select::make('enterprise_id')
                    ->relationship('enterprise', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('所属企业'),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[A-Za-z0-9_-]+$/')
                    ->unique(ignoreRecord: true)
                    ->label('组织编码'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('组织名称'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('short_name')->label('简称')->searchable()->toggleable(),
                TextColumn::make('primary_type')->label('主要类型')->searchable()->toggleable(),
                TextColumn::make('area_code')->label('地区码')->searchable()->toggleable(),
                TextColumn::make('address')->label('详细地址')->searchable()->toggleable(),
                TextColumn::make('logo')->label('Logo')->searchable()->toggleable(),
                TextColumn::make('website')->label('官方网站')->searchable()->toggleable(),
                TextColumn::make('contact_name')->label('联系人')->searchable()->toggleable(),
                TextColumn::make('contact_mobile')->label('联系电话')->searchable()->toggleable(),
                TextColumn::make('contact_email')->label('联系邮箱')->searchable()->toggleable(),
                TextColumn::make('description')->label('组织简介')->searchable()->toggleable(),
                TextColumn::make('verified_status')->label('认证状态')->searchable()->toggleable(),
                TextColumn::make('remark')->label('备注')->searchable()->toggleable(),

                TextColumn::make('enterprise.name')
                    ->label('所属企业'),
                TextColumn::make('code')
                    ->searchable()
                    ->label('组织编码'),
                TextColumn::make('name')
                    ->searchable()
                    ->label('组织名称'),
            ])
            ->filters([TernaryFilter::make('is_enabled')->label('启用状态')])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePlatformOrganizations::route('/'),
        ];
    }
}
