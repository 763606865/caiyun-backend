<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 系统基础设置表。
 *
 * @property int $id 设置主键
 * @property string $key 配置实例标识
 * @property string $site_name 站点名称
 * @property string|null $logo 站点 Logo 文件路径
 * @property string|null $favicon 站点 Favicon 文件路径
 * @property string|null $customer_service_phone 客服电话
 * @property string|null $icp_number ICP 备案号
 * @property bool $sms_enabled 是否启用短信功能
 * @property bool $registration_enabled 是否开放用户注册
 * @property string|null $default_avatar 用户默认头像文件路径
 * @property int $upload_max_size_mb 单文件上传大小限制（MB）
 * @property bool $payment_enabled 是否启用支付配置
 * @property string|null $maintenance_message 维护模式提示文案
 * @property string|null $seo_title SEO 标题
 * @property string|null $seo_keywords SEO 关键词
 * @property string|null $seo_description SEO 描述
 * @property string|null $copyright 版权信息
 * @property string|null $analytics_code 统计代码
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
#[Table(name: 'system_settings')]
#[Fillable([
    'site_name', 'logo', 'favicon', 'customer_service_phone', 'icp_number',
    'sms_enabled', 'registration_enabled', 'default_avatar',
    'upload_max_size_mb', 'payment_enabled', 'maintenance_message',
    'seo_title', 'seo_keywords', 'seo_description', 'copyright', 'analytics_code',
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
