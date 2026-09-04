<?php

namespace Tests\Feature\License;

use App\Models\LicenseInstance;
use App\Models\LicensePackage;
use App\Models\LicenseVerificationLog;
use App\Models\OpenApiCredential;
use App\Services\LicenseSigner;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
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
        config([
            'licensing.private_key' => $privateKey,
            'licensing.lease_hours' => 72,
            'licensing.public_base_url' => 'https://license.example.com',
        ]);
    }

    public function test_generated_package_matches_client_signature_and_claims(): void
    {
        $license = $this->license();
        [$header, $payload, $signature] = explode('.', $license->token, 3);

        self::assertSame(1, openssl_verify($header.'.'.$payload, $this->decode($signature), $this->publicKey, OPENSSL_ALGO_SHA256));
        $headerClaims = json_decode($this->decode($header), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(LicenseSigner::LICENSE_TYP, $headerClaims['typ']);
        self::assertSame(LicenseSigner::LICENSE_VERSION, $headerClaims['v']);

        $claims = json_decode($this->decode($payload), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($license->license_id, $claims['license_id']);
        self::assertSame(['payment', 'ai'], $claims['features']);
        self::assertSame(['example.com', '*.example.org'], $claims['domains']);
        self::assertSame($license->openApiCredential->client_id, $claims['openapi']['client_id']);
        self::assertSame($license->openApiCredential->client_secret, $claims['openapi']['client_secret']);
        self::assertSame(
            'https://license.example.com/openapi/v1/leases/valid',
            $claims['endpoints']['lease_valid'],
        );
    }

    public function test_rotating_openapi_secret_resigns_bound_license(): void
    {
        $license = $this->license();
        $oldToken = $license->token;
        $credential = $license->openApiCredential;
        $newSecret = OpenApiCredential::generateSecret();

        $credential->update(['client_secret' => $newSecret]);

        $fresh = $license->fresh();
        self::assertNotSame($oldToken, $fresh->token);
        [, $payload] = explode('.', $fresh->token, 3);
        $claims = json_decode($this->decode($payload), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($newSecret, $claims['openapi']['client_secret']);
    }

    public function test_remote_endpoint_issues_compatible_lease(): void
    {
        $license = $this->license();
        $instanceId = '550e8400-e29b-41d4-a716-446655440000';
        $this->flushHeaders();

        $response = $this->postEncrypted([
            'license' => $license->token,
            'license_id' => $license->license_id,
            'instance_id' => $instanceId,
            'domain' => 'example.com',
            'project' => 'cms',
        ], $license->openApiCredential)->assertOk();

        [$payload, $signature] = explode('.', $response->json('data.lease'), 2);
        self::assertSame(1, openssl_verify($payload, $this->decode($signature), $this->publicKey, OPENSSL_ALGO_SHA256));
        $claims = json_decode($this->decode($payload), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($license->license_id, $claims['license_id']);
        self::assertSame($instanceId, $claims['instance_id']);
        self::assertSame('example.com', $claims['domain']);
        self::assertSame(['payment', 'ai'], $claims['features']);
        self::assertTrue(CarbonImmutable::parse($claims['issued_at'])->lessThanOrEqualTo(now()));
        self::assertSame(
            72,
            (int) CarbonImmutable::parse($claims['issued_at'])->diffInHours(CarbonImmutable::parse($claims['expires_at'])),
        );
        self::assertNotNull($license->fresh()->last_verified_at);
        self::assertDatabaseHas(LicenseInstance::class, [
            'license_package_id' => $license->id,
            'instance_id' => $instanceId,
            'status' => 'active',
        ]);
        self::assertDatabaseHas(LicenseVerificationLog::class, [
            'license_package_id' => $license->id,
            'instance_id' => $instanceId,
            'result' => 'success',
        ]);
    }

    public function test_revoked_package_is_rejected_by_remote_endpoint(): void
    {
        $license = $this->license();
        $license->forceFill(['revoked_at' => now(), 'revoked_reason' => '合同终止'])->saveQuietly();

        $this->postEncrypted([
            'license' => $license->token,
            'license_id' => $license->license_id,
            'instance_id' => '550e8400-e29b-41d4-a716-446655440000',
            'domain' => 'example.com',
            'project' => 'cms',
        ], $license->openApiCredential)->assertForbidden()->assertJson(['code' => 403, 'reason' => 'revoked', 'message' => '合同终止']);
    }

    public function test_remote_endpoint_rejects_a_domain_outside_the_license(): void
    {
        $license = $this->license();

        $this->postEncrypted([
            'license' => $license->token,
            'license_id' => $license->license_id,
            'instance_id' => '550e8400-e29b-41d4-a716-446655440000',
            'domain' => 'unauthorized.example.net',
            'project' => 'cms',
        ], $license->openApiCredential)->assertForbidden()->assertJson([
            'reason' => 'domain_mismatch',
            'message' => '当前域名未获得授权',
        ]);
        self::assertDatabaseHas(LicenseVerificationLog::class, [
            'license_package_id' => $license->id,
            'result' => 'rejected',
            'reason' => 'domain_mismatch',
        ]);
    }

    public function test_remote_endpoint_validates_the_client_protocol(): void
    {
        $this->postEncrypted([])
            ->assertUnprocessable()
            ->assertJsonPath('code', 422)
            ->assertJsonMissingPath('lease');
    }

    public function test_remote_endpoint_rejects_an_invalid_signature(): void
    {
        $this->postEncrypted([], signature: 'invalid')
            ->assertUnauthorized()
            ->assertJson(['reason' => 'invalid_signature']);
    }

    public function test_remote_endpoint_rejects_a_replayed_nonce(): void
    {
        $credential = OpenApiCredential::query()->create(['name' => '测试调用方']);
        $nonce = (string) Str::uuid();

        $this->postEncrypted([], $credential, $nonce)->assertUnprocessable();
        $this->postEncrypted([], $credential, $nonce)
            ->assertConflict()
            ->assertJson(['reason' => 'replayed_request']);
    }

    private function license(): LicensePackage
    {
        $credential = OpenApiCredential::query()->create(['name' => '授权测试客户']);

        return LicensePackage::query()->create([
            'customer' => '测试客户',
            'project' => 'cms',
            'open_api_credential_id' => $credential->id,
            'plan' => 'pro',
            'features' => ['payment', 'ai'],
            'domains' => ['example.com', '*.example.org'],
            'not_before' => now()->subMinute(),
            'expires_at' => now()->addYear(),
        ])->load('openApiCredential');
    }

    private function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    }

    /** @param array<string, mixed> $payload */
    private function postEncrypted(
        array $payload,
        ?OpenApiCredential $credential = null,
        ?string $nonce = null,
        ?string $signature = null,
    ): TestResponse {
        $credential ??= OpenApiCredential::query()->create(['name' => '测试调用方']);
        $timestamp = (string) now()->timestamp;
        $nonce ??= (string) Str::uuid();
        $aad = implode("\n", [$credential->client_id, $timestamp, $nonce]);
        $iv = random_bytes(12);
        $plaintext = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            hash_hkdf('sha256', $credential->client_secret, 32, 'caiyun-openapi-encryption-v1'),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad,
        );
        self::assertIsString($ciphertext);
        $envelope = json_encode([
            'ciphertext' => $this->encode($ciphertext),
            'iv' => $this->encode($iv),
            'tag' => $this->encode($tag),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $canonical = implode("\n", ['POST', '/openapi/v1/leases/valid', $timestamp, $nonce, hash('sha256', $envelope)]);
        $signature ??= $this->encode(hash_hmac(
            'sha256',
            $canonical,
            hash_hkdf('sha256', $credential->client_secret, 32, 'caiyun-openapi-signing-v1'),
            true,
        ));

        return $this->call('POST', '/openapi/v1/leases/valid', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_OPENAPI_CLIENT_ID' => $credential->client_id,
            'HTTP_X_OPENAPI_TIMESTAMP' => $timestamp,
            'HTTP_X_OPENAPI_NONCE' => $nonce,
            'HTTP_X_OPENAPI_SIGNATURE' => $signature,
        ], content: $envelope);
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
