<?php

namespace App\Enums;

/** 组织主要类型。 */
enum OrganizationType: string
{
    case Enterprise = 'enterprise';
    case School = 'school';
    case Government = 'government';
    case PublicInstitution = 'public_institution';
    case Association = 'association';
    case LawFirm = 'law_firm';
    case HrAgency = 'hr_agency';
    case Medical = 'medical';
    case Foundation = 'foundation';
    case Community = 'community';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Enterprise => '企业',
            self::School => '学校/教育机构',
            self::Government => '党政机关',
            self::PublicInstitution => '事业单位',
            self::Association => '协会/社会团体',
            self::LawFirm => '律师事务所',
            self::HrAgency => '人力资源服务机构',
            self::Medical => '医疗卫生机构',
            self::Foundation => '基金会',
            self::Community => '基层自治组织',
            self::Other => '其他组织',
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
