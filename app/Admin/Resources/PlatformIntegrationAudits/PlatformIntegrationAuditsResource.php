<?php

namespace App\Admin\Resources\PlatformIntegrationAudits;

use App\Models\PlatformIntegrationAudit;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** 只读接入管理；所有写入由独立的受控人工绑定动作执行。 */
class PlatformIntegrationAuditsResource extends Resource
{
    protected static ?string $model = PlatformIntegrationAudit::class;

    protected static ?string $modelLabel = '接入与绑定审计';

    protected static ?string $navigationLabel = '接入与绑定审计';

    protected static string|\UnitEnum|null $navigationGroup = '中台管理';

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();

        return $admin !== null && $admin->is_active && $admin->hasRole('super-admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('application.code')->label('应用')->searchable()->sortable(),
            TextColumn::make('enterprise.name')->label('企业')->searchable()->sortable(),
            TextColumn::make('action')->label('动作')->searchable()->sortable(),
            TextColumn::make('object_key')->label('对象/管理员标识')->searchable()->sortable(),
            TextColumn::make('status')->label('状态码')->searchable()->sortable(),
            TextColumn::make('trace_id')->label('追踪ID')->searchable()->sortable(),
            TextColumn::make('created_at')->label('时间')->searchable()->sortable(),
        ])->defaultSort('id', 'desc')->recordActions([])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePlatformIntegrationAudits::route('/')];
    }
}
