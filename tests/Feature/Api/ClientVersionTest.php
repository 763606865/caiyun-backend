<?php

namespace Tests\Feature\Api;

use App\Enums\ClientPlatform;
use App\Enums\ClientType;
use App\Models\ClientVersion;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_an_optional_update_for_an_older_client(): void
    {
        $this->createVersion([
            'app_version' => '1.2.0',
            'app_build' => '120',
            'download_url' => 'https://example.com/app',
            'release_notes' => '新增功能',
        ]);

        $this->clientHeaders('1.1.0', '110')
            ->getJson('/api/client/version/check')
            ->assertOk()
            ->assertJsonPath('data.has_update', true)
            ->assertJsonPath('data.force_update', false)
            ->assertJsonPath('data.update_type', 'optional')
            ->assertJsonPath('data.latest_version.app_version', '1.2.0')
            ->assertJsonPath('data.latest_version.download_url', 'https://example.com/app');
    }

    public function test_it_forces_update_when_client_is_below_minimum_version(): void
    {
        $this->createVersion([
            'app_version' => '2.0.0',
            'app_build' => '200',
            'min_supported_version' => '1.5.0',
            'min_supported_build' => '150',
        ]);

        $this->clientHeaders('1.4.9', '149')
            ->getJson('/api/client/version/check')
            ->assertOk()
            ->assertJsonPath('data.has_update', true)
            ->assertJsonPath('data.force_update', true)
            ->assertJsonPath('data.update_type', 'force');
    }

    public function test_it_returns_no_update_for_current_version(): void
    {
        $this->createVersion([
            'app_version' => '1.2.0',
            'app_build' => '120',
            'is_force_update' => true,
        ]);

        $this->clientHeaders('1.2.0', '120')
            ->getJson('/api/client/version/check')
            ->assertOk()
            ->assertJsonPath('data.has_update', false)
            ->assertJsonPath('data.force_update', false)
            ->assertJsonPath('data.update_type', 'none');
    }

    public function test_it_ignores_other_channels_and_unpublished_versions(): void
    {
        $this->createVersion([
            'channel' => 'beta',
            'app_version' => '9.0.0',
            'app_build' => '900',
        ]);
        $this->createVersion([
            'app_version' => '8.0.0',
            'app_build' => '800',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->clientHeaders('1.0.0', '100')
            ->getJson('/api/client/version/check')
            ->assertOk()
            ->assertJsonPath('data.has_update', false)
            ->assertJsonPath('data.latest_version', null);
    }

    public function test_middleware_blocks_clients_below_the_minimum_version(): void
    {
        $this->createVersion([
            'app_version' => '2.0.0',
            'app_build' => '200',
            'min_supported_version' => '1.5.0',
            'min_supported_build' => '150',
        ]);

        $this->clientHeaders('1.4.0', '140')
            ->postJson('/api/devices/sync')
            ->assertStatus(426)
            ->assertJson([
                'code' => 426,
                'message' => '客户端版本过低，请升级后继续使用',
            ]);

        $this->assertDatabaseCount('user_devices', 0);
    }

    public function test_middleware_allows_optional_updates(): void
    {
        $this->createVersion([
            'app_version' => '2.0.0',
            'app_build' => '200',
            'min_supported_version' => '1.0.0',
            'min_supported_build' => '100',
        ]);

        $this->clientHeaders('1.5.0', '150')
            ->postJson('/api/devices/sync')
            ->assertOk();

        $this->assertDatabaseCount('user_devices', 1);
    }

    public function test_middleware_caches_the_latest_version_policy(): void
    {
        $this->createVersion([
            'app_version' => '2.0.0',
            'app_build' => '200',
            'min_supported_version' => '1.5.0',
        ]);

        $versionQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$versionQueries): void {
            if (str_contains($query->sql, 'client_versions')) {
                $versionQueries++;
            }
        });

        $this->clientHeaders('1.0.0', '100')
            ->postJson('/api/devices/sync')
            ->assertStatus(426);
        $this->clientHeaders('1.0.0', '100')
            ->postJson('/api/devices/sync')
            ->assertStatus(426);

        $this->assertSame(1, $versionQueries);
    }

    public function test_updating_a_version_invalidates_its_policy_cache(): void
    {
        $version = $this->createVersion([
            'app_version' => '2.0.0',
            'app_build' => '200',
            'min_supported_version' => '1.5.0',
        ]);

        $this->clientHeaders('1.0.0', '100')
            ->postJson('/api/devices/sync')
            ->assertStatus(426);

        $version->update([
            'min_supported_version' => '1.0.0',
            'min_supported_build' => '100',
        ]);

        $this->clientHeaders('1.0.0', '100')
            ->postJson('/api/devices/sync')
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createVersion(array $attributes = []): ClientVersion
    {
        return ClientVersion::query()->create([
            'client_type' => ClientType::AppIos,
            'platform' => ClientPlatform::Ios,
            'channel' => 'app-store',
            'app_version' => '1.0.0',
            'app_build' => '100',
            'is_force_update' => false,
            'is_published' => true,
            'published_at' => now()->subMinute(),
            ...$attributes,
        ]);
    }

    private function clientHeaders(string $version, string $build): static
    {
        return $this->withHeaders([
            'X-Client-Type' => 'app-ios',
            'X-App-Version' => $version,
            'X-App-Build' => $build,
            'X-Platform' => 'ios',
            'X-OS-Version' => '18.2',
            'X-Device-ID' => 'ios-version-test',
            'X-Channel' => 'app-store',
        ]);
    }
}
