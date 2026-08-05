<?php

namespace App\Admin\Resources\Media;

use App\Admin\Resources\Media\Pages\CreateMedia;
use App\Admin\Resources\Media\Pages\EditMedia;
use App\Admin\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = '媒体库';

    protected static ?string $modelLabel = '媒体';

    protected static string|UnitEnum|null $navigationGroup = '内容中心';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([FileUpload::make('path')->label('文件')->disk('public')->directory('cms/media')->required()->openable()->downloadable(), TextInput::make('title')->label('标题'), TextInput::make('alt')->label('替代文本'), TextInput::make('folder')->label('分组')->default('/')]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([ImageColumn::make('path')->label('预览')->disk('public'), TextColumn::make('name')->label('文件名')->searchable(), TextColumn::make('mime_type')->label('类型'), TextColumn::make('size')->label('大小')->formatStateUsing(fn (int $state) => number_format($state / 1024, 1).' KB'), TextColumn::make('folder')->label('分组'), TextColumn::make('created_at')->label('上传时间')->dateTime()])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMedia::route('/'), 'create' => CreateMedia::route('/create'), 'edit' => EditMedia::route('/{record}/edit')];
    }
}
