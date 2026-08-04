<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->binary('uuid', 16)->unique()->comment('全局唯一用户标识(UUID v4/v7)');
            $table->string('real_name')->nullable()->comment('真实姓名');
            $table->string('nick_name', 50)->nullable()->comment('昵称');
            $table->string('phone', 30)->nullable()->unique();
            $table->string('avatar')->nullable()->comment('头像');
            $table->tinyInteger('gender')->nullable()->default(0)->comment('性别');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('status')->default(1)->comment('用户状态');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone', 30)->primary()->comment('申请重置密码的手机号');
            $table->string('token')->comment('密码重置令牌的哈希值');
            $table->timestamp('created_at')->nullable()->comment('令牌创建时间，用于判断是否过期');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
