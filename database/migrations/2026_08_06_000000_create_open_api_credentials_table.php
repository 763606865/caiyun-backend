<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_api_credentials', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('调用方名称');
            $table->string('client_id', 80)->unique();
            $table->text('client_secret')->comment('Laravel 加密存储的调用密钥');
            $table->json('allowed_ips')->nullable()->comment('允许的 IP/CIDR，空表示不限');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->ipAddress('last_used_ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_api_credentials');
    }
};
