<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Support\HtmlSanitizer;
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
use Illuminate\Support\Facades\Auth;

/**
 * CMS 内容表。
 *
 * @property int $id 内容主键
 * @property ContentType $type 内容类型
 * @property ContentStatus $status 发布状态
 * @property string $title 标题
 * @property string|null $subtitle 副标题
 * @property string $slug URL 标识
 * @property string|null $summary 摘要
 * @property string|null $body 正文
 * @property string|null $cover 封面文件路径
 * @property string|null $external_url 外部链接
 * @property string|null $source 内容来源
 * @property string|null $author_name 作者名称
 * @property int|null $created_by 创建管理员 ID
 * @property int|null $updated_by 最近更新管理员 ID
 * @property int|null $published_by 发布管理员 ID
 * @property bool $is_featured 是否推荐
 * @property bool $is_pinned 是否置顶
 * @property int $sort 排序值
 * @property int $view_count 浏览次数
 * @property Carbon|null $published_at 发布时间
 * @property Carbon|null $offline_at 下线时间
 * @property string|null $seo_title SEO 标题
 * @property string|null $seo_keywords SEO 关键词
 * @property string|null $seo_description SEO 描述
 * @property string|null $canonical_url 规范链接
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $deleted_at 软删除时间
 * @property-read Collection<int, Category> $categories 所属分类
 * @property-read Collection<int, Tag> $tags 内容标签
 * @property-read Collection<int, ContentRevision> $revisions 内容修订记录
 * @property-read AdminUser|null $creator 创建管理员
 * @property-read AdminUser|null $publisher 发布管理员
 *
 * @method static Builder<static> published() 只查询当前可访问的已发布内容
 */
#[Table(name: 'contents')]
#[Fillable(['type', 'status', 'title', 'subtitle', 'slug', 'summary', 'body', 'cover', 'external_url', 'source', 'author_name', 'is_featured', 'is_pinned', 'sort', 'published_at', 'offline_at', 'seo_title', 'seo_keywords', 'seo_description', 'canonical_url'])]
class Content extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $content): void {
            $content->body = HtmlSanitizer::clean($content->body);
        });
        static::creating(function (self $content): void {
            $adminId = filter_var(Auth::guard('admin')->id(), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($adminId !== false) {
                $content->created_by ??= $adminId;
                $content->updated_by ??= $adminId;
            }
        });
        static::updating(function (self $content): void {
            $adminId = filter_var(Auth::guard('admin')->id(), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($adminId !== false) {
                $content->updated_by = $adminId;
            }
        });
        static::saved(function (self $content): void {
            if (! $content->wasRecentlyCreated && ! $content->wasChanged(['title', 'subtitle', 'summary', 'body', 'cover', 'status'])) {
                return;
            }
            $content->revisions()->create([
                'admin_user_id' => Auth::guard('admin')->id(),
                'version' => ((int) $content->revisions()->max('version')) + 1,
                'snapshot' => $content->only($content->getFillable()),
            ]);
        });
    }

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'offline_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<ContentRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    /** @return BelongsTo<AdminUser, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /** @return BelongsTo<AdminUser, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'published_by');
    }

    /**
     * @param  Builder<Content>  $query
     * @return Builder<Content>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('offline_at')->orWhere('offline_at', '>', now()));
    }

    public function publish(?int $adminUserId = null): void
    {
        $this->forceFill([
            'status' => ContentStatus::Published,
            'published_at' => $this->published_at ?? now(),
            'published_by' => $adminUserId ?? Auth::guard('admin')->id(),
        ])->save();
    }
}
