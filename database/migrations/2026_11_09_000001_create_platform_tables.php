<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_applications', function (Blueprint $table) {
            $table->comment('中台应用注册表');

            $table->id();
            $table->string('code')->comment('唯一编码')->unique();
            $table->string('name')->comment('名称');
            $table->string('base_url')->comment('应用访问地址');
            $table->boolean('is_enabled')->comment('是否启用')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_enterprises', function (Blueprint $table) {
            $table->comment('中台企业表');

            $table->id();
            $table->string('code')->comment('唯一编码')->unique();
            $table->string('name')->comment('名称');
            $table->boolean('is_enabled')->comment('是否启用')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_organizations', function (Blueprint $table) {
            $table->comment('中台组织表');

            $table->id();
            $table->foreignId('enterprise_id')
                ->comment('所属企业 ID')
                ->constrained('platform_enterprises')
                ->restrictOnDelete();
            $table->string('code')->comment('唯一编码')->unique();
            $table->string('name')->comment('名称');
            $table->timestamps();
        });

        Schema::create('platform_identity_mappings', function (Blueprint $table) {
            $table->comment('中台同步映射表');

            $table->id();
            $table->foreignId('application_id')
                ->comment('所属应用 ID')
                ->constrained('platform_applications')
                ->restrictOnDelete();
            $table->foreignId('enterprise_id')
                ->comment('所属企业 ID')
                ->constrained('platform_enterprises')
                ->restrictOnDelete();
            $table->string('entity_type')->comment('同步实体类型：enterprise 企业、organization 组织、user 用户');
            $table->string('external_id')->comment('应用内实体 ID');
            $table->string('canonical_id')->comment('中台实体记录 ID');
            $table->unique(['application_id', 'enterprise_id', 'entity_type', 'external_id'], 'platform_mapping_external_unique');
            $table->timestamps();
        });

        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->comment('企业应用套餐表');

            $table->id();
            $table->foreignId('application_id')
                ->comment('所属应用 ID')
                ->constrained('platform_applications')
                ->restrictOnDelete();
            $table->foreignId('enterprise_id')
                ->comment('所属企业 ID')
                ->constrained('platform_enterprises')
                ->restrictOnDelete();
            $table->string('package_name')->comment('套餐名称');
            $table->unsignedInteger('seat_limit')->comment('用户额度');
            $table->dateTime('starts_at')->comment('套餐开始时间');
            $table->dateTime('ends_at')->comment('套餐结束时间');
            $table->boolean('is_enabled')->comment('是否启用')->default(true);
            $table->unique(['application_id', 'enterprise_id']);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('platform_subscriptions');
        Schema::dropIfExists('platform_identity_mappings');
        Schema::dropIfExists('platform_organizations');
        Schema::dropIfExists('platform_enterprises');
        Schema::dropIfExists('platform_applications');
    }
};
