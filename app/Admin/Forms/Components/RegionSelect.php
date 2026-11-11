<?php

namespace App\Admin\Forms\Components;

use App\Support\ChinaRegions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/** 省市区联动，辅助字段不入库，只保存 area_code。 */
class RegionSelect extends Group
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->columns(3)->schema([
            Select::make('province_code')
                ->label('省份')
                ->placeholder('请选择省份')
                ->options(ChinaRegions::provinces())
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->afterStateUpdated(function (Set $set, ?string $state): void {
                    $set('city_code', null);
                    $set('district_code', null);
                    $set('area_code', $state);
                }),
            Select::make('city_code')
                ->label('城市')
                ->placeholder('请选择城市')
                ->options(fn (Get $get): array => ChinaRegions::cities($get->string('province_code', isNullable: true)))
                ->disabled(fn (Get $get): bool => blank($get('province_code')))
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                    $set('district_code', null);
                    $set('area_code', $state ?: $get('province_code'));
                }),
            Select::make('district_code')
                ->label('区县')
                ->placeholder(fn (Get $get): string => filled($get('city_code')) && ChinaRegions::districts($get->string('city_code')) === []
                    ? '该市无区县选项'
                    : '请选择区县')
                ->options(fn (Get $get): array => ChinaRegions::districts($get->string('city_code', isNullable: true)))
                ->disabled(fn (Get $get): bool => ChinaRegions::districts($get->string('city_code', isNullable: true)) === [])
                ->searchable()
                ->live()
                ->dehydrated(false)
                ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => $set('area_code', $state ?: $get('city_code') ?: $get('province_code'))),
            Hidden::make('area_code')
                ->afterStateHydrated(function (Set $set, ?string $state): void {
                    $path = ChinaRegions::path($state);
                    $set('province_code', $path['province']);
                    $set('city_code', $path['city']);
                    $set('district_code', $path['district']);
                }),
        ]);
    }
}
