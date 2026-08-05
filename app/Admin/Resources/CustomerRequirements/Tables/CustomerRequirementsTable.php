<?php

namespace App\Admin\Resources\CustomerRequirements\Tables;

use App\Enums\RequirementStatus;
use App\Enums\RequirementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerRequirementsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('organization')->label('公司 / 组织')->searchable()->sortable(),
            TextColumn::make('contact_name')->label('联系人')->searchable(),
            TextColumn::make('contact_method')->label('联系方式')->searchable()->copyable(),
            TextColumn::make('requirement_type')->label('需求类型')->badge()
                ->formatStateUsing(fn (RequirementType $state): string => $state->label()),
            TextColumn::make('status')->label('跟进状态')->badge()
                ->formatStateUsing(fn (RequirementStatus $state): string => $state->label())
                ->color(fn (RequirementStatus $state): string => $state->color()),
            TextColumn::make('assignedAdmin.name')->label('负责人')->placeholder('未分配'),
            TextColumn::make('followed_up_at')->label('最近跟进')->dateTime()->placeholder('尚未跟进')->sortable(),
            TextColumn::make('created_at')->label('提交时间')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('requirement_type')->label('需求类型')->options(RequirementType::options()),
            SelectFilter::make('status')->label('跟进状态')->options(RequirementStatus::options()),
            SelectFilter::make('assigned_admin_id')->label('负责人')->relationship('assignedAdmin', 'name'),
        ])->recordActions([
            EditAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }
}
