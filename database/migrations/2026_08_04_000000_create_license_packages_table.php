<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('license_id', 80)->unique()->comment('对外授权编号');
            $table->string('customer')->comment('客户名称');
            $table->string('project')->nullable()->index()->comment('项目标识');
            $table->string('plan')->default('standard')->comment('授权套餐');
            $table->json('features')->comment('功能权益');
            $table->json('domains')->comment('授权域名');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('not_before')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->longText('token')->comment('RSA 签名授权包');
            $table->timestamp('revoked_at')->nullable()->index();
            $table->string('revoked_reason')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_packages');
    }
};
