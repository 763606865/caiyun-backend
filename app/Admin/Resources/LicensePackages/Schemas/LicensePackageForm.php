<?php

namespace App\Admin\Resources\LicensePackages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LicensePackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('license_id')->label('授权编号')->placeholder('留空自动生成')->maxLength(80)->unique(ignoreRecord: true),
            TextInput::make('customer')->label('客户名称')->required()->maxLength(255),
            TextInput::make('project')->label('项目标识')->helperText('开启远程验证时与客户端 LICENSE_PROJECT 一致')->maxLength(100),
            TextInput::make('plan')->label('套餐')->required()->default('standard')->maxLength(100),
            TagsInput::make('domains')->label('授权域名')->placeholder('example.com')->helperText('支持精确域名及 *.example.com')->required()->columnSpanFull(),
            TagsInput::make('features')->label('功能权益')->placeholder('payment')->required()->columnSpanFull(),
            DateTimePicker::make('not_before')->label('生效时间')->required()->default(now())->seconds(false),
            DateTimePicker::make('expires_at')->label('到期时间')->required()->default(now()->addYear())->after('not_before')->seconds(false),
            Textarea::make('token')->label('签名授权包')->disabled()->dehydrated(false)->rows(5)->columnSpanFull(),
        ]);
    }
}
