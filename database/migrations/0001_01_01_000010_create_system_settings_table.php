<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建系统设置表。
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->default('default')->unique()->comment('配置实例标识');
            $table->string('site_name')->comment('站点名称');
            $table->string('logo')->nullable()->comment('站点 Logo 文件路径');
            $table->string('favicon')->nullable()->comment('站点 Favicon 文件路径');
            $table->string('customer_service_phone', 50)->nullable()->comment('客服电话');
            $table->string('icp_number', 100)->nullable()->comment('ICP 备案号');
            $table->boolean('sms_enabled')->default(true)->comment('是否启用短信功能');
            $table->boolean('registration_enabled')->default(true)->comment('是否开放用户注册');
            $table->string('default_avatar')->nullable()->comment('用户默认头像文件路径');
            $table->unsignedInteger('upload_max_size_mb')->default(10)->comment('单文件上传大小限制，单位 MB');
            $table->boolean('payment_enabled')->default(false)->comment('是否启用支付配置');
            $table->text('maintenance_message')->nullable()->comment('维护模式提示文案');
            $table->string('seo_title')->nullable();
            $table->string('seo_keywords', 500)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('copyright')->nullable();
            $table->string('analytics_code', 2000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
