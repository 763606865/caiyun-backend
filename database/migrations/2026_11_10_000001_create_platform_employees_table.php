<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_employees', function (Blueprint $table) {
            $table->comment('企业职工用户关联表');
            $table->id();
            $table->foreignId('enterprise_id')->comment('所属企业 ID')->constrained('platform_enterprises')->restrictOnDelete();
            $table->foreignId('user_id')->comment('职工用户 ID')->constrained('users')->restrictOnDelete();
            $table->string('employee_no')->nullable()->comment('企业内职工编号');
            $table->string('position')->nullable()->comment('职务');
            $table->boolean('is_enabled')->default(true)->comment('是否在职启用');
            $table->unique(['enterprise_id', 'user_id']);
            $table->unique(['enterprise_id', 'employee_no']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_employees');
    }
};
