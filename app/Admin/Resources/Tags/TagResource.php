<?php

namespace App\Admin\Resources\Tags;

use App\Admin\Resources\Tags\Pages\CreateTag;
use App\Admin\Resources\Tags\Pages\EditTag;
use App\Admin\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = '标签管理';

    protected static ?string $modelLabel = '标签';

    protected static string|UnitEnum|null $navigationGroup = '内容中心';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label('名称')->required(), TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true), Textarea::make('description')->label('描述')]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('名称')->searchable(), TextColumn::make('slug')->label('Slug')->searchable(), TextColumn::make('contents_count')->label('内容数')->counts('contents')])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTags::route('/'), 'create' => CreateTag::route('/create'), 'edit' => EditTag::route('/{record}/edit')];
    }
}
