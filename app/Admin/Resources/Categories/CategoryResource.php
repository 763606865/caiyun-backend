<?php

namespace App\Admin\Resources\Categories;

use App\Admin\Resources\Categories\Pages\CreateCategory;
use App\Admin\Resources\Categories\Pages\EditCategory;
use App\Admin\Resources\Categories\Pages\ListCategories;
use App\Enums\CategoryType;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $navigationLabel = '栏目管理';

    protected static ?string $modelLabel = '栏目';

    protected static string|UnitEnum|null $navigationGroup = '内容中心';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $types = collect(CategoryType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();

        return $schema->columns(2)->components([
            Select::make('parent_id')->label('上级栏目')->relationship('parent', 'name')->searchable()->preload(),
            Select::make('type')->label('类型')->options($types)->required()->default(CategoryType::List->value),
            TextInput::make('name')->label('名称')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, $set, $record) => $record ?: $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            TextInput::make('external_url')->label('外部链接')->url(),
            TextInput::make('sort')->label('排序')->numeric()->default(0),
            Toggle::make('is_enabled')->label('启用')->default(true),
            Textarea::make('description')->label('描述')->columnSpanFull(),
            Section::make('SEO')->collapsed()->columnSpanFull()->columns(2)->schema([
                TextInput::make('seo_title')->label('SEO 标题'), TextInput::make('seo_keywords')->label('关键词'),
                Textarea::make('seo_description')->label('描述')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->columns([
            TextColumn::make('name')->label('名称')->searchable(), TextColumn::make('parent.name')->label('上级'),
            TextColumn::make('type')->label('类型')->formatStateUsing(fn (CategoryType $state) => $state->label()),
            TextColumn::make('slug')->label('Slug'), IconColumn::make('is_enabled')->label('启用')->boolean(), TextColumn::make('sort')->label('排序')->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCategories::route('/'), 'create' => CreateCategory::route('/create'), 'edit' => EditCategory::route('/{record}/edit')];
    }
}
