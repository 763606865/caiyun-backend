<?php

namespace App\Admin\Resources\SystemSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SystemSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('site_name')
                    ->label('站点名称')
                    ->required()
                    ->maxLength(255),
                TextInput::make('customer_service_phone')
                    ->label('客服电话')
                    ->tel()
                    ->maxLength(50),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->directory('system-settings')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048),
                FileUpload::make('favicon')
                    ->label('Favicon')
                    ->disk('public')
                    ->directory('system-settings')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/png',
                        'image/svg+xml',
                        'image/x-icon',
                        'image/vnd.microsoft.icon',
                    ])
                    ->maxSize(1024),
                FileUpload::make('default_avatar')
                    ->label('默认头像')
                    ->disk('public')
                    ->directory('system-settings')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->avatar()
                    ->maxSize(2048),
                TextInput::make('icp_number')
                    ->label('ICP 备案号')
                    ->placeholder('例如：京ICP备12345678号')
                    ->maxLength(100),
                TextInput::make('upload_max_size_mb')
                    ->label('单文件上传限制')
                    ->numeric()
                    ->required()
                    ->default(10)
                    ->minValue(1)
                    ->maxValue(10240)
                    ->suffix('MB'),
                Toggle::make('sms_enabled')
                    ->label('短信功能')
                    ->helperText('关闭后业务代码可据此停止发送短信。')
                    ->default(true),
                Toggle::make('registration_enabled')
                    ->label('开放注册')
                    ->default(true),
                Toggle::make('payment_enabled')
                    ->label('支付配置')
                    ->default(false),
                Textarea::make('maintenance_message')
                    ->label('维护模式提示')
                    ->placeholder('系统维护期间向用户展示的提示文案')
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('seo_title')->label('SEO 默认标题')->maxLength(255),
                TextInput::make('seo_keywords')->label('SEO 默认关键词')->maxLength(500),
                Textarea::make('seo_description')->label('SEO 默认描述')->rows(3)->columnSpanFull(),
                TextInput::make('copyright')->label('版权信息')->maxLength(255),
                Textarea::make('analytics_code')->label('统计代码')->helperText('仅保存统计平台标识或配置，前台输出时请勿直接信任 HTML。')->columnSpanFull(),
            ]);
    }
}
