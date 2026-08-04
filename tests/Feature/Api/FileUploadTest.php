<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('oss');
        config(['upload.disk' => 'oss']);
    }

    public function test_authenticated_user_can_upload_a_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api/files/upload', [
            'file' => UploadedFile::fake()->image('avatar.jpg'),
            'directory' => 'avatar',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.message', '文件上传成功')
            ->assertJsonPath('data.file.original_name', 'avatar.jpg')
            ->assertJsonPath('data.file.extension', 'jpg');

        $path = $response->json('data.file.path');
        $this->assertIsString($path);
        $this->assertMatchesRegularExpression(
            '#^avatar/\d{4}/\d{2}/\d{2}/[0-9a-f-]+\.jpg$#',
            $path,
        );
        Storage::disk('oss')->assertExists($path);
    }

    public function test_upload_requires_authentication(): void
    {
        $this->postJson('/api/files/upload')
            ->assertUnauthorized()
            ->assertJsonPath('code', 401);
    }

    public function test_upload_rejects_unsupported_extension_and_directory(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/api/files/upload', [
            'file' => UploadedFile::fake()->create('script.php', 1, 'text/x-php'),
            'directory' => '../unsafe',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file', 'directory']);
    }
}
