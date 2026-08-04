<?php

namespace Tests\Feature\Models;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use App\Models\ClientVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_client_release_and_casts_version_attributes(): void
    {
        $version = ClientVersion::query()->create([
            'client_type' => ClientType::AppIos,
            'platform' => ClientPlatform::Ios,
            'channel' => 'app-store',
            'app_version' => '1.2.0',
            'app_build' => '120',
            'min_supported_version' => '1.0.0',
            'min_supported_build' => '100',
            'is_force_update' => true,
            'is_published' => true,
            'download_url' => 'https://example.com/app',
            'release_notes' => '修复已知问题',
            'published_at' => now()->subMinute(),
        ]);

        $this->assertSame(ClientType::AppIos, $version->client_type);
        $this->assertSame(ClientPlatform::Ios, $version->platform);
        $this->assertTrue($version->is_force_update);
        $this->assertTrue($version->is_published);
        $this->assertNotNull($version->published_at);
        $this->assertTrue(ClientVersion::query()->published()->whereKey($version)->exists());
    }

    public function test_published_scope_excludes_drafts_and_future_releases(): void
    {
        foreach ([
            ['is_published' => false, 'published_at' => null],
            ['is_published' => true, 'published_at' => now()->addDay()],
        ] as $state) {
            ClientVersion::query()->create([
                'client_type' => ClientType::AppAndroid,
                'platform' => ClientPlatform::Android,
                'app_version' => '1.0.0',
                'app_build' => fake()->unique()->numerify('###'),
                ...$state,
            ]);
        }

        $this->assertSame(0, ClientVersion::query()->published()->count());
    }
}
