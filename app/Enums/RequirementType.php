<?php

namespace App\Enums;

enum RequirementType: string
{
    case WebsiteCms = 'website-cms';
    case WebAdmin = 'web-admin';
    case MobileApp = 'mobile-app';
    case ProductTrial = 'product-trial';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WebsiteCms => '官网 / CMS',
            self::WebAdmin => 'Web / 管理应用',
            self::MobileApp => '移动应用',
            self::ProductTrial => '产品试用',
            self::Other => '其他',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
