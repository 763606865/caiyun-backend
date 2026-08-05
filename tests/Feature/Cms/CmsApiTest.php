<?php

namespace Tests\Feature\Cms;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CmsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_currently_published_contents_are_public(): void
    {
        Content::query()->create(['title' => '已发布', 'slug' => 'published', 'status' => ContentStatus::Published, 'published_at' => now()->subMinute()]);
        Content::query()->create(['title' => '草稿', 'slug' => 'draft', 'status' => ContentStatus::Draft]);
        Content::query()->create(['title' => '未到时间', 'slug' => 'future', 'status' => ContentStatus::Published, 'published_at' => now()->addDay()]);

        $this->getJson('/api/cms/contents')->assertOk()->assertJsonCount(1, 'data.data')->assertJsonPath('data.data.0.slug', 'published');
        $this->getJson('/api/cms/contents/draft')->assertNotFound();
    }

    public function test_contents_can_be_filtered_by_enabled_category(): void
    {
        $category = Category::query()->create(['name' => '公告', 'slug' => 'notices']);
        $content = Content::query()->create(['title' => '公告一', 'slug' => 'notice-1', 'status' => ContentStatus::Published, 'published_at' => now()]);
        $content->categories()->attach($category);

        $this->getJson('/api/cms/contents?category=notices')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson('/api/cms/contents?category=other')->assertOk()->assertJsonCount(0, 'data.data');
    }

    public function test_preview_requires_a_valid_signature(): void
    {
        $content = Content::query()->create(['title' => '草稿', 'slug' => 'preview', 'status' => ContentStatus::Draft]);
        $this->get("/cms/preview/{$content->id}")->assertForbidden();
        $this->get(URL::temporarySignedRoute('cms.preview', now()->addMinute(), ['content' => $content]))->assertOk()->assertSee('草稿');
    }

    public function test_rich_text_is_sanitized_before_storage(): void
    {
        $content = Content::query()->create(['title' => '安全', 'slug' => 'safe', 'body' => '<p onclick="bad()">ok<script>alert(1)</script><a href="javascript:bad()">link</a></p>']);
        self::assertStringNotContainsString('onclick', (string) $content->body);
        self::assertStringNotContainsString('<script', (string) $content->body);
        self::assertStringNotContainsString('javascript:', (string) $content->body);
    }
}
