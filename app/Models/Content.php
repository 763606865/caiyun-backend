<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
    public function scopePublished(Builder $query): Builder
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
