<?php

namespace App\Admin\Resources\OpenApiCredentials\Schemas;

use App\Models\OpenApiCredential;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OpenApiCredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('调用方名称')->required()->maxLength(255),
            TextInput::make('client_id')->label('Client ID')->required()->unique(ignoreRecord: true)
                ->default(fn (): string => 'opk_'.Str::lower(Str::random(32)))->maxLength(80)->copyable(),
            TextInput::make('client_secret')->label('Client Secret')->password()->revealable()->required()
                ->default(fn (): string => OpenApiCredential::generateSecret())->helperText('请通过安全渠道派发，泄露后应立即轮换。')->columnSpanFull(),
            TagsInput::make('allowed_ips')->label('允许 IP / CIDR')->placeholder('203.0.113.10')
                ->helperText('留空表示不限制；支持单个 IP 和 CIDR。')->columnSpanFull(),
            DateTimePicker::make('expires_at')->label('凭据过期时间')->seconds(false),
            Toggle::make('is_active')->label('启用')->default(true),
        ]);
    }
}
