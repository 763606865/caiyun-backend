<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 内容修订记录表。
 *
 * @property int $id 修订记录主键
 * @property int $content_id 内容 ID
 * @property int|null $admin_user_id 操作管理员 ID
 * @property int $version 修订版本号
 * @property array<string, mixed> $snapshot 内容快照
 * @property string|null $note 修订说明
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property-read Content $content 所属内容
 * @property-read AdminUser|null $adminUser 操作管理员
 */
#[Table(name: 'content_revisions')]
#[Fillable(['admin_user_id', 'version', 'snapshot', 'note'])]
class ContentRevision extends Model
{
    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }

    /** @return BelongsTo<Content, $this> */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /** @return BelongsTo<AdminUser, $this> */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }
}
