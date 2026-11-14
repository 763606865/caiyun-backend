<?php

namespace App\Admin\Resources\PlatformDirectorySources\Pages;

use App\Admin\Resources\PlatformDirectorySources\PlatformDirectorySourcesResource;
use App\Models\PlatformApplication;
use App\Models\PlatformEnterprise;
use App\Services\Platform\ManualDirectoryBinding;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePlatformDirectorySources extends ManageRecords
{
    protected static string $resource = PlatformDirectorySourcesResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('bindSource')->label('预检 / 确认来源绑定')
            ->visible(fn (): bool => PlatformDirectorySourcesResource::canAccess())
            ->requiresConfirmation()
            ->modalDescription('默认只预检。确认应用、企业、稳定来源ID和统一身份后，勾选确认才写入；已有绑定不能覆盖。用户为全局身份，需人工核实其真实归属。')
            ->schema([
                Select::make('application_id')->label('来源应用')->options(fn () => PlatformApplication::query()->pluck('name', 'id'))->required()->searchable(),
                Select::make('enterprise_uuid')->label('企业')->options(fn () => PlatformEnterprise::query()->pluck('name', 'uuid'))->required()->searchable(),
                Select::make('entity_type')->label('类型')->options(['enterprise' => '企业', 'user' => '用户', 'organization' => '组织', 'employment' => '任职'])->required(),
                TextInput::make('source_id')->label('稳定来源ID')->required()->maxLength(191),
                TextInput::make('entity_uuid')->label('统一身份UUID')->required()->uuid(),
                Toggle::make('apply')->label('已人工核实，确认写入绑定')->default(false),
            ])->action(function (array $data): void {
                $result = app(ManualDirectoryBinding::class)->execute(auth('admin')->user(), $data);
                Notification::make()->success()->title($result['applied'] ? '绑定已确认，未覆盖历史映射' : '预检通过，尚未写入绑定')->send();
            })];
    }
}
