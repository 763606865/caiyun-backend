<?php

namespace Tests\Feature\License;

use App\Models\LicenseInstance;
use App\Models\LicensePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_detector_marks_a_stale_instance_as_abnormal(): void
    {
        config(['licensing.private_key' => $this->privateKey()]);
        $license = LicensePackage::query()->create([
            'customer' => '测试客户', 'project' => 'cms', 'plan' => 'pro',
            'features' => ['*'], 'domains' => ['example.com'],
            'not_before' => now()->subMonth(), 'expires_at' => now()->addYear(),
        ]);
        $instance = LicenseInstance::query()->create([
            'license_package_id' => $license->id,
            'instance_id' => '550e8400-e29b-41d4-a716-446655440000',
            'domain' => 'example.com', 'status' => 'active',
            'first_verified_at' => now()->subDays(9), 'last_verified_at' => now()->subDays(8),
        ]);

        $this->artisan('license:detect-abnormal')->assertSuccessful();

        self::assertSame('abnormal', $instance->fresh()->status);
        self::assertNotNull($instance->fresh()->abnormal_notified_at);
    }

    private function privateKey(): string
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        self::assertTrue(openssl_pkey_export($key, $privateKey));

        return $privateKey;
    }
}
