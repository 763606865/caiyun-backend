<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 后台操作日志表。
 *
 * @property int $id 操作日志主键
 * @property int|null $admin_user_id 操作管理员 ID
 * @property string $action 操作类型
 * @property string|null $subject_type 操作对象类型
 * @property int|null $subject_id 操作对象 ID
 * @property array<string, mixed>|null $before 操作前数据
 * @property array<string, mixed>|null $after 操作后数据
 * @property string|null $ip 操作 IP
 * @property string|null $user_agent User-Agent
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read AdminUser|null $adminUser 操作管理员
 * @property-read Model|null $subject 操作对象
 */
#[Table(name: 'operation_logs')]
#[Fillable(['admin_user_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'ip', 'user_agent'])]
class OperationLog extends Model
{
    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array'];
    }

    /** @return BelongsTo<AdminUser, $this> */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
