<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 内容标签表。
 *
 * @property int $id 标签主键
 * @property string $name 标签名称
 * @property string $slug URL 标识
 * @property string|null $description 标签描述
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 软删除时间
 * @property-read Collection<int, Content> $contents 使用该标签的内容
 */
#[Table(name: 'tags')]
#[Fillable(['name', 'slug', 'description'])]
class Tag extends Model
{
    use SoftDeletes;

    /** @return BelongsToMany<Content, $this> */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class);
    }
}
