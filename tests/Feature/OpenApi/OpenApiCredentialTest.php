<?php

namespace Tests\Feature\OpenApi;

use App\Models\OpenApiCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OpenApiCredentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_secret_is_encrypted_at_rest(): void
    {
        $secret = OpenApiCredential::generateSecret();
        $credential = OpenApiCredential::query()->create([
            'name' => '甲方测试项目',
            'client_secret' => $secret,
        ]);

        self::assertSame($secret, $credential->client_secret);
        self::assertNotSame($secret, DB::table('open_api_credentials')->where('id', $credential->id)->value('client_secret'));
        self::assertStringStartsWith('opk_', $credential->client_id);
    }
}
