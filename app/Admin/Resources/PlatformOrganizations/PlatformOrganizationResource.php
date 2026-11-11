<?php

namespace App\Admin\Resources\PlatformOrganizations;

use App\Admin\Forms\Components\RegionSelect;
use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationStatus;
use App\Models\PlatformOrganization;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
                Select::make('primary_type')
                    ->label('主要类型')
                    ->options(OrganizationType::options()),
                RegionSelect::make()->columnSpanFull(),
                TextInput::make('address')
                    ->label('详细地址')
                    ->maxLength(255),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->directory('organizations/logos')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->fetchFileInformation(fn (?PlatformOrganization $record): bool => ! Str::isUrl($record->logo ?? '', ['http', 'https']))
                    ->getUploadedFileUsing(function (FileUpload $component, string $file): ?array {
                        if (Str::isUrl($file, ['http', 'https'])) {
                            return ['name' => basename(parse_url($file, PHP_URL_PATH) ?: 'Logo'), 'size' => 0, 'type' => null, 'url' => $file];
                        }

                        return $component->getUploadedFile($file, null);
                    }),
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
                Select::make('verified_status')
                    ->label('认证状态')
                    ->options(function (?PlatformOrganization $record): array {
                        $options = OrganizationVerificationStatus::options();

                        if (filled($record?->verified_status) && ! isset($options[$record->verified_status])) {
                            $options[$record->verified_status] = $record->verified_status;
                        }

                        return $options;
                    })
                    ->default(OrganizationVerificationStatus::Unverified->value),
                TextInput::make('remark')
                    ->label('备注')
                    ->maxLength(255),
                Toggle::make('is_enabled')->label('启用')->default(true),
                Select::make('enterprise_id')
                    ->relationship('enterprise', 'name')
                    ->searchable()
                    ->preload()
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
                TextColumn::make('primary_type')
                    ->label('主要类型')
                    ->formatStateUsing(fn (?OrganizationType $state): ?string => $state?->label())
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('area_code')->label('地区码')->searchable()->toggleable(),
                TextColumn::make('address')->label('详细地址')->searchable()->toggleable(),
                TextColumn::make('logo')->label('Logo')->searchable()->toggleable(),
                TextColumn::make('website')->label('官方网站')->searchable()->toggleable(),
                TextColumn::make('contact_name')->label('联系人')->searchable()->toggleable(),
                TextColumn::make('contact_mobile')->label('联系电话')->searchable()->toggleable(),
                TextColumn::make('contact_email')->label('联系邮箱')->searchable()->toggleable(),
                TextColumn::make('description')->label('组织简介')->searchable()->toggleable(),
                TextColumn::make('verified_status')
                    ->label('认证状态')
                    ->formatStateUsing(fn (?string $state): ?string => OrganizationVerificationStatus::tryFrom($state ?? '')?->label() ?? $state)
                    ->searchable()
                    ->toggleable(),
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
