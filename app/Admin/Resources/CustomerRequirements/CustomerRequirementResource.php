<?php

namespace App\Admin\Resources\CustomerRequirements;

use App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement;
use App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement;
use App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements;
use App\Admin\Resources\CustomerRequirements\Schemas\CustomerRequirementForm;
use App\Admin\Resources\CustomerRequirements\Tables\CustomerRequirementsTable;
use App\Models\CustomerRequirement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerRequirementResource extends Resource
{
    protected static ?string $model = CustomerRequirement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = '客户需求';

    protected static ?string $modelLabel = '客户需求';

    protected static ?string $pluralModelLabel = '客户需求';

    protected static string|\UnitEnum|null $navigationGroup = '客户管理';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'organization';

    public static function form(Schema $schema): Schema
    {
        return CustomerRequirementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerRequirementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerRequirements::route('/'),
            'create' => CreateCustomerRequirement::route('/create'),
            'edit' => EditCustomerRequirement::route('/{record}/edit'),
        ];
    }
}
