<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 中台组织表。
 *
 * @property string|null $short_name 简称
 * @property string|null $primary_type 主要类型
 * @property string|null $area_code 地区码
 * @property string|null $address 详细地址
 * @property string|null $logo Logo
 * @property string|null $website 官方网站
 * @property string|null $contact_name 联系人
 * @property string|null $contact_mobile 联系电话
 * @property string|null $contact_email 联系邮箱
 * @property string|null $description 组织简介
 * @property string|null $verified_status 认证状态
 * @property string|null $remark 备注
 * @property int $id 主键
 * @property int $enterprise_id 所属企业 ID
 * @property string $code 唯一编码
 * @property string $name 名称
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read PlatformEnterprise $enterprise 所属企业
 */
#[Table(name: 'platform_organizations')]
#[Fillable([
    'short_name',
    'primary_type',
    'area_code',
    'address',
    'logo',
    'website',
    'contact_name',
    'contact_mobile',
    'contact_email',
    'description',
    'verified_status',
    'remark',
    'parent_id',
    'is_enabled',

    'enterprise_id',
    'code',
    'name',
])]
class PlatformOrganization extends Model
{
    /** @return BelongsTo<PlatformEnterprise, $this> */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(PlatformEnterprise::class);
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
