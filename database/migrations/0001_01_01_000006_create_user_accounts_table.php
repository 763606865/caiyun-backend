<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 创建用户第三方账号表。
     *
     * provider + app_id 用于隔离同一平台下的不同应用，provider_account_id
     * 保存平台内稳定的用户标识（例如微信 openid）。
     */
    public function up(): void
    {
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('所属用户 ID')->constrained()->cascadeOnDelete();
            $table->string('provider', 32)->comment('第三方平台：wechat/alipay/douyin 等');
            $table->string('app_id', 128)->default('default')->comment('第三方应用 ID');
            $table->string('provider_account_id', 191)->comment('平台账号唯一标识，例如微信 openid');
            $table->string('union_id', 191)->nullable()->comment('平台跨应用统一标识，例如微信 unionid');
            $table->string('mobile', 32)->nullable()->comment('第三方平台认证的手机号');
            $table->string('nickname')->nullable()->comment('第三方平台昵称');
            $table->text('avatar_url')->nullable()->comment('第三方平台头像地址');
            $table->json('data')->nullable()->comment('平台扩展信息，不保存 access_token 等敏感凭证');
            $table->timestamp('last_login_at')->nullable()->comment('最近一次通过该账号登录时间');
            $table->timestamps();

            $table->unique(
                ['provider', 'app_id', 'provider_account_id'],
                'user_accounts_provider_identity_unique',
            );
            $table->unique(
                ['user_id', 'provider', 'app_id'],
                'user_accounts_user_provider_unique',
            );
            $table->index(['provider', 'union_id']);
            $table->index('mobile');
        });
    }

    /**
     * 删除用户第三方账号表。
     */
    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
