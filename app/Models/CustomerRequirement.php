<?php

namespace App\Models;

use App\Enums\RequirementStatus;
use App\Enums\RequirementType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户需求表。
 *
 * @property int $id 客户需求主键
 * @property string|null $contact_name 联系人姓名
 * @property string $contact_method 电话、邮箱或其他联系方式
 * @property string|null $organization 公司或组织名称
 * @property RequirementType $requirement_type 需求类型
 * @property string|null $requirements 项目需求详情
 * @property RequirementStatus $status 跟进状态
 * @property int|null $assigned_admin_id 跟进负责人管理员 ID
 * @property string|null $follow_up_notes 内部跟进备注
 * @property CarbonImmutable|null $followed_up_at 最近跟进时间
 * @property string|null $source_ip 客户提交 IP
 * @property string|null $user_agent 客户提交 User-Agent
 * @property CarbonImmutable|null $created_at 创建时间
 * @property CarbonImmutable|null $updated_at 更新时间
 * @property-read AdminUser|null $assignedAdmin 跟进负责人
 */
#[Table(name: 'customer_requirements')]
#[Fillable([
    'contact_name', 'contact_method', 'organization', 'requirement_type', 'requirements',
    'status', 'assigned_admin_id', 'follow_up_notes', 'followed_up_at', 'source_ip', 'user_agent',
])]
class CustomerRequirement extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => RequirementStatus::Pending->value,
    ];

    /** @return BelongsTo<AdminUser, $this> */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'assigned_admin_id');
    }

    protected static function booted(): void
    {
        static::updating(function (CustomerRequirement $requirement): void {
            if ($requirement->isDirty(['status', 'assigned_admin_id', 'follow_up_notes'])
                && ! $requirement->isDirty('followed_up_at')) {
                $requirement->followed_up_at = now();
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requirement_type' => RequirementType::class,
            'status' => RequirementStatus::class,
            'followed_up_at' => 'immutable_datetime',
        ];
    }
}
