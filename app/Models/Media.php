<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * 媒体文件表。
 *
 * @property int $id 媒体文件主键
 * @property int|null $admin_user_id 上传管理员 ID
 * @property string $disk 存储磁盘
 * @property string $path 存储路径
 * @property string $name 原始文件名
 * @property string|null $mime_type MIME 类型
 * @property string|null $extension 文件扩展名
 * @property int $size 文件大小（字节）
 * @property string|null $hash 文件哈希
 * @property string|null $title 媒体标题
 * @property string|null $alt 替代文本
 * @property string $folder 所属文件夹
 * @property int|null $width 图片宽度（像素）
 * @property int|null $height 图片高度（像素）
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 软删除时间
 * @property-read string $url 文件访问地址
 * @property-read AdminUser|null $adminUser 上传管理员
 */
#[Table(name: 'media')]
#[Fillable(['admin_user_id', 'disk', 'path', 'name', 'mime_type', 'extension', 'size', 'hash', 'title', 'alt', 'folder', 'width', 'height'])]
class Media extends Model
{
    use SoftDeletes;

    protected $appends = ['url'];

    /** @return BelongsTo<AdminUser, $this> */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
