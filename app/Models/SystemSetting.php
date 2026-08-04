<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 系统基础设置。
 *
 * @property int $id
 * @property string $key
 * @property string $site_name
 * @property string|null $logo
 * @property string|null $favicon
 * @property string|null $customer_service_phone
 * @property string|null $icp_number
 * @property bool $sms_enabled
 * @property bool $registration_enabled
 * @property string|null $default_avatar
 * @property int $upload_max_size_mb
 * @property bool $payment_enabled
 * @property string|null $maintenance_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'site_name', 'logo', 'favicon', 'customer_service_phone', 'icp_number',
    'sms_enabled', 'registration_enabled', 'default_avatar',
    'upload_max_size_mb', 'payment_enabled', 'maintenance_message',
])]
class SystemSetting extends Model
{
    public const DEFAULT_KEY = 'default';

    /** @var array<string, mixed> */
    protected $attributes = [
        'key' => self::DEFAULT_KEY,
        'sms_enabled' => true,
        'registration_enabled' => true,
        'upload_max_size_mb' => 10,
        'payment_enabled' => false,
    ];

    /**
     * 获取当前系统设置，尚未创建时返回带默认值的新模型。
     */
    public static function current(): self
    {
        return self::query()->where('key', self::DEFAULT_KEY)->first() ?? new self;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sms_enabled' => 'boolean',
            'registration_enabled' => 'boolean',
            'upload_max_size_mb' => 'integer',
            'payment_enabled' => 'boolean',
        ];
    }
}
