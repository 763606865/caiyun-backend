<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建客户端版本控制表。
     */
    public function up(): void
    {
        Schema::create('client_versions', function (Blueprint $table) {
            $table->id();
            $table->string('client_type', 20)->comment('web/wechat-mini/app-ios/app-android');
            $table->string('platform', 20)->comment('ios/android/wechat/web');
            $table->string('channel', 50)->default('official')->comment('安装或发布渠道');
            $table->string('app_version', 50)->comment('对用户展示的客户端版本号');
            $table->string('app_build', 50)->comment('客户端构建号');
            $table->string('min_supported_version', 50)->nullable()->comment('最低支持的客户端版本号');
            $table->string('min_supported_build', 50)->nullable()->comment('最低支持的客户端构建号');
            $table->boolean('is_force_update')->default(false)->comment('是否强制更新');
            $table->boolean('is_published')->default(false)->comment('是否已发布');
            $table->text('download_url')->nullable()->comment('客户端下载或应用商店地址');
            $table->text('release_notes')->nullable()->comment('版本更新说明');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamps();

            $table->unique(
                ['client_type', 'channel', 'app_version', 'app_build'],
                'client_versions_release_unique',
            );
            $table->index(
                ['client_type', 'channel', 'is_published', 'published_at'],
                'client_versions_latest_index',
            );
            $table->index(['platform', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_versions');
    }
};
