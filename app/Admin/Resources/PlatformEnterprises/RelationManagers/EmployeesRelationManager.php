<?php

namespace App\Admin\Resources\PlatformEnterprises\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/** 企业下的职工管理，操作仅影响当前企业的关联记录。 */
class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = '职工';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('用户')
                ->relationship('user', 'phone')
                ->searchable()
                ->preload()
                ->required()
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('enterprise_id', $this->getOwnerRecord()->getKey()),
                ),
            TextInput::make('employee_no')
                ->label('职工编号')
                ->maxLength(255)
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('enterprise_id', $this->getOwnerRecord()->getKey()),
                ),
            TextInput::make('position')->label('职务')->maxLength(255),
            Toggle::make('is_enabled')->label('在职启用')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.nick_name')->label('用户')->searchable(),
                TextColumn::make('user.phone')->label('手机号')->searchable(),
                TextColumn::make('employee_no')->label('职工编号')->searchable(),
                TextColumn::make('position')->label('职务'),
                IconColumn::make('is_enabled')->label('在职启用')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
