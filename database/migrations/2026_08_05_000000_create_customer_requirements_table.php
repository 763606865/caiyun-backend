<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_requirements', function (Blueprint $table): void {
            $table->id();
            $table->string('contact_name', 100)->nullable()->comment('联系人');
            $table->string('contact_method', 255)->comment('电话、邮箱或其他联系方式');
            $table->string('organization', 255)->nullable()->comment('公司或组织名称');
            $table->string('requirement_type', 30)->comment('需求类型');
            $table->text('requirements')->nullable()->comment('项目需求');
            $table->tinyInteger('status')->default(0)->comment('跟进状态:0-待跟进,1-已跟进,2-已完成,3-已关闭');
            $table->foreignId('assigned_admin_id')->nullable()->comment('跟进负责人')
                ->constrained('admin_users')->nullOnDelete();
            $table->text('follow_up_notes')->nullable()->comment('内部跟进备注');
            $table->dateTime('followed_up_at')->nullable()->comment('最近跟进时间');
            $table->string('source_ip', 45)->nullable()->comment('提交 IP');
            $table->string('user_agent', 500)->nullable()->comment('提交 User-Agent');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->comment('客户需求及跟进信息表');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_requirements');
    }
};
