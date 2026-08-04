<?php

namespace App\Admin\Resources\SystemSettings;

use App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting;
use App\Admin\Resources\SystemSettings\Pages\EditSystemSetting;
use App\Admin\Resources\SystemSettings\Pages\ListSystemSettings;
use App\Admin\Resources\SystemSettings\Schemas\SystemSettingForm;
use App\Admin\Resources\SystemSettings\Tables\SystemSettingsTable;
use App\Models\SystemSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = '系统设置';

    protected static ?string $modelLabel = '系统设置';

    protected static ?string $pluralModelLabel = '系统设置';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'site_name';

    public static function canCreate(): bool
    {
        return parent::canCreate()
            && ! SystemSetting::query()->where('key', SystemSetting::DEFAULT_KEY)->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return SystemSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemSettings::route('/'),
            'create' => CreateSystemSetting::route('/create'),
            'edit' => EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
