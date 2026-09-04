<?php

namespace App\Admin\Resources\LicensePackages\Schemas;

use App\Models\OpenApiCredential;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
            TextInput::make('project')->label('项目标识')->helperText('开启远程验证时与客户端项目标识一致')->maxLength(100),
            Select::make('open_api_credential_id')
                ->label('OpenAPI 凭据')
                ->relationship('openApiCredential', 'name')
                ->getOptionLabelFromRecordUsing(
                    fn (OpenApiCredential $record): string => "{$record->name} ({$record->client_id})"
                )
                ->searchable()
                ->preload()
                ->helperText('留空则创建授权包时自动新建一组 Client ID / Secret，并写入授权书。')
                ->createOptionForm([
                    TextInput::make('name')->label('调用方名称')->required()->maxLength(255),
                ])
                ->createOptionUsing(fn (array $data): int => OpenApiCredential::query()->create($data)->getKey()),
            TextInput::make('plan')->label('套餐')->required()->default('standard')->maxLength(100),
            TagsInput::make('domains')->label('授权域名')->placeholder('example.com')->helperText('支持精确域名及 *.example.com')->required()->columnSpanFull(),
            TagsInput::make('features')->label('功能权益')->placeholder('payment')->required()->columnSpanFull(),
            DateTimePicker::make('not_before')->label('生效时间')->required()->default(now())->seconds(false),
            DateTimePicker::make('expires_at')->label('到期时间')->required()->default(now()->addYear())->after('not_before')->seconds(false),
            Textarea::make('token')->label('签名授权书')->disabled()->dehydrated(false)->rows(5)->columnSpanFull()
                ->helperText('完整授权书请通过列表「下载授权书」获取；文件含 OpenAPI 凭据，请通过安全渠道交付。'),
        ]);
    }
}
