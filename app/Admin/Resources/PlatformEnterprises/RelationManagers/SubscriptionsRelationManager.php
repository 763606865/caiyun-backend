<?php

namespace App\Admin\Resources\PlatformEnterprises\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/** 企业下的应用套餐管理，操作仅影响当前企业的关联记录。 */
class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = '应用套餐';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('application_id')
                ->label('应用')
                ->relationship('application', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('enterprise_id', $this->getOwnerRecord()->getKey()),
                ),
            TextInput::make('package_name')->label('套餐名称')->maxLength(255)->required(),
            TextInput::make('seat_limit')->label('用户额度')->numeric()->integer()->minValue(1)->maxValue(4294967295)->required(),
            DateTimePicker::make('starts_at')->label('开始时间')->required(),
            DateTimePicker::make('ends_at')->label('结束时间')->after('starts_at')->required(),
            Toggle::make('is_enabled')->label('启用')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.name')->label('应用'),
                TextColumn::make('package_name')->label('套餐名称')->searchable(),
                TextColumn::make('seat_limit')->label('用户额度'),
                TextColumn::make('starts_at')->label('开始时间')->dateTime(),
                TextColumn::make('ends_at')->label('结束时间')->dateTime(),
                IconColumn::make('is_enabled')->label('启用')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
