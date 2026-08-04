<?php

namespace App\Admin\Resources\ClientVersions\Schemas;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClientVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('client_type')
                    ->label('客户端类型')
                    ->options(self::clientTypeOptions())
                    ->required(),
                Select::make('platform')
                    ->label('运行平台')
                    ->options(self::platformOptions())
                    ->required(),
                TextInput::make('channel')
                    ->label('发布渠道')
                    ->required()
                    ->default('official')
                    ->maxLength(50),
                TextInput::make('download_url')
                    ->label('下载地址')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('app_version')
                    ->label('版本号')
                    ->placeholder('例如：1.2.0')
                    ->required()
                    ->maxLength(50),
                TextInput::make('app_build')
                    ->label('构建号')
                    ->placeholder('例如：10200')
                    ->required()
                    ->maxLength(50),
                TextInput::make('min_supported_version')
                    ->label('最低支持版本号')
                    ->maxLength(50),
                TextInput::make('min_supported_build')
                    ->label('最低支持构建号')
                    ->maxLength(50),
                Toggle::make('is_force_update')
                    ->label('强制更新')
                    ->default(false),
                Toggle::make('is_published')
                    ->label('已发布')
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label('发布时间')
                    ->requiredIf('is_published', true)
                    ->seconds(false),
                Textarea::make('release_notes')
                    ->label('更新说明')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<string, string> */
    private static function clientTypeOptions(): array
    {
        return [
            ClientType::Web->value => '网页',
            ClientType::WechatMini->value => '微信小程序',
            ClientType::AppIos->value => 'iOS App',
            ClientType::AppAndroid->value => 'Android App',
        ];
    }

    /** @return array<string, string> */
    private static function platformOptions(): array
    {
        return [
            ClientPlatform::Ios->value => 'iOS',
            ClientPlatform::Android->value => 'Android',
            ClientPlatform::Wechat->value => '微信',
            ClientPlatform::Web->value => '网页',
        ];
    }
}
