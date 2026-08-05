<?php

namespace App\Admin\Resources\CustomerRequirements\Schemas;

use App\Enums\RequirementStatus;
use App\Enums\RequirementType;
use App\Models\AdminUser;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerRequirementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('客户提交信息')->columns(2)->schema([
                TextInput::make('contact_name')->label('联系人')->maxLength(100),
                TextInput::make('contact_method')->label('联系方式')->required()->maxLength(255),
                TextInput::make('organization')->label('公司 / 组织名称')->maxLength(255),
                Select::make('requirement_type')->label('需求类型')->options(RequirementType::options())->required(),
                Textarea::make('requirements')->label('项目需求')->rows(8)->maxLength(10000)->columnSpanFull(),
            ])->columnSpanFull(),
            Section::make('内部跟进')->columns(2)->schema([
                Select::make('status')->label('跟进状态')->options(RequirementStatus::options())->required()->default(RequirementStatus::Pending->value),
                Select::make('assigned_admin_id')->label('负责人')->options(fn (): array => AdminUser::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())->searchable(),
                DateTimePicker::make('followed_up_at')->label('最近跟进时间')->seconds(false),
                Textarea::make('follow_up_notes')->label('内部备注')->rows(6)->maxLength(10000)->columnSpanFull(),
            ])->columnSpanFull(),
        ]);
    }
}
