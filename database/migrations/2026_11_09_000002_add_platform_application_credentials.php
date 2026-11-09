<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_applications', function (Blueprint $table) {
            $table->string('app_key', 32)->nullable()->unique()->comment('应用公开凭证 APP_KEY');
            $table->text('app_secret')->nullable()->comment('加密存储的 APP_SECRET');
            $table->json('allowed_ips')->nullable()->comment('IP 白名单，空表示不限制');
            $table->dateTime('expires_at')->nullable()->comment('凭证过期时间，空表示长期有效');
            $table->string('webhook_url', 500)->nullable()->comment('事件回调地址');
            $table->text('remark')->nullable()->comment('备注');
        });
        DB::table('platform_applications')->orderBy('id')->each(function ($app) {
            DB::table('platform_applications')->where('id', $app->id)->update(['app_key' => bin2hex(random_bytes(16)), 'app_secret' => Crypt::encryptString(bin2hex(random_bytes(32)))]);
        });
    }

    public function down(): void
    {
        Schema::table('platform_applications', function (Blueprint $table) {
            $table->dropUnique(['app_key']);
            $table->dropColumn(['app_key', 'app_secret', 'allowed_ips', 'expires_at', 'webhook_url', 'remark']);
        });
    }
};
