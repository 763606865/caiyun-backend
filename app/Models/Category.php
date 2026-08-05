<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 内容分类表。
 *
 * @property int $id 分类主键
 * @property int|null $parent_id 父分类 ID
 * @property string $name 分类名称
 * @property string $slug URL 标识
 * @property CategoryType $type 分类类型
 * @property string|null $external_url 外部链接
 * @property string|null $description 分类描述
 * @property string|null $seo_title SEO 标题
 * @property string|null $seo_keywords SEO 关键词
 * @property string|null $seo_description SEO 描述
 * @property int $sort 排序值
 * @property bool $is_enabled 是否启用
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 软删除时间
 * @property-read Category|null $parent 父分类
 * @property-read Collection<int, Category> $children 子分类
 * @property-read Collection<int, Content> $contents 分类下的内容
 *
 * @method static Builder<static> enabled() 只查询已启用的分类
 */
#[Table(name: 'categories')]
#[Fillable(['parent_id', 'name', 'slug', 'type', 'external_url', 'description', 'seo_title', 'seo_keywords', 'seo_description', 'sort', 'is_enabled'])]
class Category extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['type' => CategoryType::class, 'is_enabled' => 'boolean'];
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /** @return BelongsToMany<Content, $this> */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class);
    }

    /**
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    #[Scope]
    protected function enabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
