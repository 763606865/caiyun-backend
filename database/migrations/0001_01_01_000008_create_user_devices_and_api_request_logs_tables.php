<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建设备与 API 请求日志表。
     */
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->comment('最近使用该设备的用户 ID')
                ->constrained()->nullOnDelete();
            $table->string('device_id', 191)->unique()->comment('客户端安装实例 ID');
            $table->string('client_type', 20)->comment('web/wechat-mini/app-ios/app-android');
            $table->string('app_version', 50)->comment('客户端版本号');
            $table->string('app_build', 50)->comment('客户端构建号');
            $table->string('platform', 20)->comment('ios/android/wechat/web');
            $table->string('os_version', 50)->comment('操作系统版本');
            $table->string('channel', 50)->comment('安装或发布渠道');
            $table->string('last_ip', 45)->nullable()->comment('最近请求 IP');
            $table->text('user_agent')->nullable()->comment('最近请求 User-Agent');
            $table->timestamp('first_seen_at')->comment('首次请求时间');
            $table->timestamp('last_seen_at')->comment('最近请求时间');
            $table->timestamps();

            $table->index(['client_type', 'app_version']);
            $table->index(['platform', 'channel']);
            $table->index('last_seen_at');
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->comment('请求链路 ID');
            $table->foreignId('user_id')->nullable()->comment('请求用户 ID')
                ->constrained()->nullOnDelete();
            $table->foreignId('user_device_id')->nullable()->comment('请求设备记录 ID')
                ->constrained()->nullOnDelete();
            $table->string('method', 10)->comment('HTTP 请求方法');
            $table->string('path', 500)->comment('不含查询参数的请求路径');
            $table->string('route_name')->nullable()->comment('Laravel 路由名称');
            $table->unsignedSmallInteger('status_code')->comment('HTTP 响应状态码');
            $table->unsignedInteger('duration_ms')->comment('服务端处理耗时，毫秒');
            $table->string('ip', 45)->nullable()->comment('请求 IP');
            $table->timestamp('requested_at')->comment('请求时间');

            $table->index('request_id');
            $table->index(['user_id', 'requested_at']);
            $table->index(['user_device_id', 'requested_at']);
            $table->index(['status_code', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('user_devices');
    }
};
