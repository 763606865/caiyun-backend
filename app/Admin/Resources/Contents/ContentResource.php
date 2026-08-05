<?php

namespace App\Admin\Resources\Contents;

use App\Admin\Resources\Contents\Pages\CreateContent;
use App\Admin\Resources\Contents\Pages\EditContent;
use App\Admin\Resources\Contents\Pages\ListContents;
use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use UnitEnum;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = '内容管理';

    protected static ?string $modelLabel = '内容';

    protected static string|UnitEnum|null $navigationGroup = '内容中心';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('内容')->columnSpan(2)->schema([
                Select::make('type')->label('类型')->options(self::enumOptions(ContentType::cases()))->required()->default(ContentType::Article->value),
                TextInput::make('title')->label('标题')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $record) => $record ?: $set('slug', Str::slug((string) $state))),
                TextInput::make('subtitle')->label('副标题')->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('summary')->label('摘要')->rows(3)->columnSpanFull(),
                RichEditor::make('body')->label('正文')->columnSpanFull(),
                TextInput::make('external_url')->label('外部链接')->url(),
                TextInput::make('source')->label('来源'),
                TextInput::make('author_name')->label('作者'),
            ])->columns(2),
            Section::make('发布')->columnSpan(1)->schema([
                Select::make('status')->label('状态')->options(self::enumOptions(ContentStatus::cases()))->required()->default(ContentStatus::Draft->value),
                DateTimePicker::make('published_at')->label('发布时间')->seconds(false),
                DateTimePicker::make('offline_at')->label('下线时间')->seconds(false)->after('published_at'),
                Select::make('categories')->label('栏目')->relationship('categories', 'name')->multiple()->preload()->searchable(),
                Select::make('tags')->label('标签')->relationship('tags', 'name')->multiple()->preload()->searchable(),
                FileUpload::make('cover')->label('封面')->disk('public')->directory('cms/covers')->image()->imageEditor(),
                Toggle::make('is_featured')->label('推荐'),
                Toggle::make('is_pinned')->label('置顶'),
                TextInput::make('sort')->label('排序')->numeric()->default(0),
            ]),
            Section::make('SEO')->columnSpanFull()->columns(2)->collapsed()->schema([
                TextInput::make('seo_title')->label('SEO 标题'),
                TextInput::make('canonical_url')->label('Canonical URL')->url(),
                TextInput::make('seo_keywords')->label('关键词'),
                Textarea::make('seo_description')->label('描述')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('title')->label('标题')->searchable()->sortable()->limit(40),
            TextColumn::make('type')->label('类型')->formatStateUsing(fn (ContentType $state) => $state->label()),
            TextColumn::make('status')->label('状态')->badge()->formatStateUsing(fn (ContentStatus $state) => $state->label()),
            TextColumn::make('categories.name')->label('栏目')->badge(),
            IconColumn::make('is_featured')->label('推荐')->boolean(),
            IconColumn::make('is_pinned')->label('置顶')->boolean(),
            TextColumn::make('published_at')->label('发布时间')->dateTime()->sortable(),
            TextColumn::make('updated_at')->label('更新时间')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->label('状态')->options(self::enumOptions(ContentStatus::cases())),
            SelectFilter::make('type')->label('类型')->options(self::enumOptions(ContentType::cases())),
            TrashedFilter::make(),
        ])->recordActions([
            Action::make('publish')->label('发布')->icon(Heroicon::OutlinedPaperAirplane)->requiresConfirmation()
                ->visible(fn (Content $record) => $record->getRawOriginal('status') !== ContentStatus::Published->value && Auth::guard('admin')->user()?->can('contents.publish'))
                ->action(fn (Content $record) => $record->publish()),
            Action::make('preview')->label('预览')->icon(Heroicon::OutlinedEye)
                ->url(fn (Content $record) => URL::temporarySignedRoute('cms.preview', now()->addHour(), ['content' => $record]))->openUrlInNewTab(),
            EditAction::make(), DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make(),
        ])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make(), ForceDeleteBulkAction::make()])]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => ListContents::route('/'), 'create' => CreateContent::route('/create'), 'edit' => EditContent::route('/{record}/edit')];
    }

    /**
     * @param  array<int, ContentStatus|ContentType>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases): array
    {
        return collect($cases)->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }
}
