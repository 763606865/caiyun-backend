<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 中台同步映射表。
 *
 * @property int $id 主键
 * @property int $application_id 所属应用 ID
 * @property int $enterprise_id 所属企业 ID
 * @property string $entity_type 同步实体类型：enterprise 企业、organization 组织、user 用户
 * @property string $external_id 应用内实体 ID
 * @property string $canonical_id 中台实体记录 ID
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read PlatformApplication $application 所属应用
 * @property-read PlatformEnterprise $enterprise 所属企业
 */
#[Table(name: 'platform_identity_mappings')]
#[Fillable([
    'application_id',
    'enterprise_id',
    'entity_type',
    'external_id',
    'canonical_id',
])]
class PlatformIdentityMapping extends Model
{
    /** @return BelongsTo<PlatformApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PlatformApplication::class);
    }

    /** @return BelongsTo<PlatformEnterprise, $this> */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(PlatformEnterprise::class);
    }
}
