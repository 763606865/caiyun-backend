<?php

namespace App\Admin\Resources\OpenApiCredentials;

use App\Admin\Resources\OpenApiCredentials\Pages\CreateOpenApiCredential;
use App\Admin\Resources\OpenApiCredentials\Pages\EditOpenApiCredential;
use App\Admin\Resources\OpenApiCredentials\Pages\ListOpenApiCredentials;
use App\Admin\Resources\OpenApiCredentials\Schemas\OpenApiCredentialForm;
use App\Admin\Resources\OpenApiCredentials\Tables\OpenApiCredentialsTable;
use App\Models\OpenApiCredential;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpenApiCredentialResource extends Resource
{
    protected static ?string $model = OpenApiCredential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'OpenAPI 密钥';

    protected static ?string $modelLabel = 'OpenAPI 密钥';

    protected static ?string $pluralModelLabel = 'OpenAPI 密钥';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OpenApiCredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpenApiCredentialsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpenApiCredentials::route('/'),
            'create' => CreateOpenApiCredential::route('/create'),
            'edit' => EditOpenApiCredential::route('/{record}/edit'),
        ];
    }
}
