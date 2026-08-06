<?php

namespace App\Admin\Resources\LicensePackages;

use App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage;
use App\Admin\Resources\LicensePackages\Pages\EditLicensePackage;
use App\Admin\Resources\LicensePackages\Pages\ListLicensePackages;
use App\Admin\Resources\LicensePackages\RelationManagers\InstancesRelationManager;
use App\Admin\Resources\LicensePackages\RelationManagers\VerificationLogsRelationManager;
use App\Admin\Resources\LicensePackages\Schemas\LicensePackageForm;
use App\Admin\Resources\LicensePackages\Tables\LicensePackagesTable;
use App\Models\LicensePackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LicensePackageResource extends Resource
{
    protected static ?string $model = LicensePackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = '授权包管理';

    protected static ?string $modelLabel = '授权包';

    protected static ?string $pluralModelLabel = '授权包';

    protected static string|\UnitEnum|null $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'license_id';

    public static function form(Schema $schema): Schema
    {
        return LicensePackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LicensePackagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLicensePackages::route('/'),
            'create' => CreateLicensePackage::route('/create'),
            'edit' => EditLicensePackage::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            InstancesRelationManager::class,
            VerificationLogsRelationManager::class,
        ];
    }
}
