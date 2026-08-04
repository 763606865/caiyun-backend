<?php

namespace Tests\Feature\License;

use App\Models\LicensePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicensePackageTest extends TestCase
{
    use RefreshDatabase;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        self::assertTrue(openssl_pkey_export($key, $privateKey));
        $details = openssl_pkey_get_details($key);
        self::assertIsArray($details);
        $this->publicKey = $details['key'];
        config(['licensing.private_key' => $privateKey, 'licensing.lease_hours' => 72]);
    }

    public function test_generated_package_matches_client_signature_and_claims(): void
    {
        $license = $this->license();
        [$payload, $signature] = explode('.', $license->token, 2);

        self::assertSame(1, openssl_verify($payload, $this->decode($signature), $this->publicKey, OPENSSL_ALGO_SHA256));
        $claims = json_decode($this->decode($payload), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($license->license_id, $claims['license_id']);
        self::assertSame(['payment', 'ai'], $claims['features']);
        self::assertSame(['example.com', '*.example.org'], $claims['domains']);
    }

    public function test_remote_endpoint_issues_compatible_lease(): void
    {
        $license = $this->license();
        $instanceId = '550e8400-e29b-41d4-a716-446655440000';

        $response = $this->postJson('/api/v1/leases', [
            'license' => $license->token,
            'license_id' => $license->license_id,
            'instance_id' => $instanceId,
            'domain' => 'example.com',
            'project' => 'cms',
        ])->assertOk();

        [$payload, $signature] = explode('.', $response->json('lease'), 2);
        self::assertSame(1, openssl_verify($payload, $this->decode($signature), $this->publicKey, OPENSSL_ALGO_SHA256));
        $claims = json_decode($this->decode($payload), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($license->license_id, $claims['license_id']);
        self::assertSame($instanceId, $claims['instance_id']);
        self::assertSame('example.com', $claims['domain']);
        self::assertSame(['payment', 'ai'], $claims['features']);
    }

    public function test_revoked_package_is_rejected_by_remote_endpoint(): void
    {
        $license = $this->license();
        $license->forceFill(['revoked_at' => now(), 'revoked_reason' => '合同终止'])->saveQuietly();

        $this->postJson('/api/v1/leases', [
            'license' => $license->token,
            'license_id' => $license->license_id,
            'instance_id' => '550e8400-e29b-41d4-a716-446655440000',
            'domain' => 'example.com',
            'project' => 'cms',
        ])->assertForbidden()->assertJson(['reason' => 'revoked', 'message' => '合同终止']);
    }

    private function license(): LicensePackage
    {
        return LicensePackage::query()->create([
            'customer' => '测试客户',
            'project' => 'cms',
            'plan' => 'pro',
            'features' => ['payment', 'ai'],
            'domains' => ['example.com', '*.example.org'],
            'not_before' => now()->subMinute(),
            'expires_at' => now()->addYear(),
        ]);
    }

    private function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    }
}
