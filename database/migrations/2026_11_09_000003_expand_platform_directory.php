<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_enterprises', function (Blueprint $table) {
            $table->string('short_name')->nullable()->comment('简称');
            $table->string('credit_code')->nullable()->comment('统一社会信用代码');
            $table->string('contact_name')->nullable()->comment('联系人');
            $table->string('contact_mobile')->nullable()->comment('联系电话');
            $table->string('address')->nullable()->comment('企业地址');
            $table->string('description')->nullable()->comment('企业简介');
            $table->string('remark')->nullable()->comment('备注');
            $table->foreignId('admin_user_id')->nullable()->comment('企业管理员用户 ID')->constrained('users')->restrictOnDelete();
        });
        Schema::table('platform_organizations', function (Blueprint $table) {
            $table->string('short_name')->nullable()->comment('简称');
            $table->string('primary_type')->nullable()->comment('主要类型');
            $table->string('area_code')->nullable()->comment('地区码');
            $table->string('address')->nullable()->comment('详细地址');
            $table->string('logo')->nullable()->comment('Logo');
            $table->string('website')->nullable()->comment('官方网站');
            $table->string('contact_name')->nullable()->comment('联系人');
            $table->string('contact_mobile')->nullable()->comment('联系电话');
            $table->string('contact_email')->nullable()->comment('联系邮箱');
            $table->string('description')->nullable()->comment('组织简介');
            $table->string('verified_status')->nullable()->comment('认证状态');
            $table->string('remark')->nullable()->comment('备注');
            $table->foreignId('parent_id')->nullable()->comment('上级组织 ID')->constrained('platform_organizations')->restrictOnDelete();
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('source')->nullable()->comment('用户来源应用编码');
            $table->dateTime('last_login_at')->nullable()->comment('最后登录时间');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['source', 'last_login_at']);
        });
        Schema::table('platform_organizations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['short_name', 'primary_type', 'area_code', 'address', 'logo', 'website', 'contact_name', 'contact_mobile', 'contact_email', 'description', 'verified_status', 'remark', 'parent_id', 'is_enabled']);
        });
        Schema::table('platform_enterprises', function (Blueprint $table) {
            $table->dropForeign(['admin_user_id']);
            $table->dropColumn(['short_name', 'credit_code', 'contact_name', 'contact_mobile', 'address', 'description', 'remark', 'admin_user_id']);
        });
    }
};
