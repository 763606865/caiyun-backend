<?php

namespace App\Admin\Resources\ClientVersions;

use App\Admin\Resources\ClientVersions\Pages\CreateClientVersion;
use App\Admin\Resources\ClientVersions\Pages\EditClientVersion;
use App\Admin\Resources\ClientVersions\Pages\ListClientVersions;
use App\Admin\Resources\ClientVersions\Schemas\ClientVersionForm;
use App\Admin\Resources\ClientVersions\Tables\ClientVersionsTable;
use App\Models\ClientVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientVersionResource extends Resource
{
    protected static ?string $model = ClientVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = '客户端版本';

    protected static ?string $modelLabel = '客户端版本';

    protected static ?string $pluralModelLabel = '客户端版本';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'app_version';

    public static function form(Schema $schema): Schema
    {
        return ClientVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientVersionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientVersions::route('/'),
            'create' => CreateClientVersion::route('/create'),
            'edit' => EditClientVersion::route('/{record}/edit'),
        ];
    }
}
