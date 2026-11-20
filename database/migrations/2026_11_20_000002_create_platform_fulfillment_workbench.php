<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_fulfillment_operators', function (Blueprint $table): void {
            $table->foreignId('enterprise_id')->constrained('platform_enterprises')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->boolean('can_retry')->default(false);
            $table->primary(['enterprise_id', 'admin_user_id']);
            $table->index('admin_user_id');
        });

        Schema::create('platform_fulfillment_failures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('platform_enterprises')->restrictOnDelete();
            $table->foreignId('application_id')->constrained('platform_applications')->restrictOnDelete();
            $table->uuid('message_uuid');
            $table->uuid('correlation_uuid');
            $table->string('kind', 32);
            $table->string('direction', 8);
            $table->string('reason', 20);
            $table->unsignedInteger('attempts')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('observed_at', 3);
            $table->unique(['enterprise_id', 'application_id', 'message_uuid', 'direction'], 'fulfillment_failure_identity');
            $table->index(['enterprise_id', 'correlation_uuid']);
        });

        Schema::create('platform_fulfillment_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('platform_enterprises')->restrictOnDelete();
            $table->uuid('correlation_uuid');
            $table->uuid('subject_uuid');
            $table->string('kind', 16);
            $table->string('subtype', 32);
            $table->json('left_snapshot')->nullable();
            $table->json('right_snapshot')->nullable();
            $table->timestamp('left_observed_at', 3)->nullable();
            $table->timestamp('right_observed_at', 3)->nullable();
            $table->string('status', 16);
            $table->timestamp('status_since');
            $table->unique(['enterprise_id', 'kind', 'subject_uuid'], 'fulfillment_check_identity');
            $table->index(['enterprise_id', 'correlation_uuid']);
            $table->index(['enterprise_id', 'status', 'status_since'], 'fulfillment_check_status');
        });

        Schema::create('platform_fulfillment_retries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('enterprise_id')->constrained('platform_enterprises')->restrictOnDelete();
            $table->foreignId('application_id')->constrained('platform_applications')->restrictOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->uuid('message_uuid');
            $table->uuid('correlation_uuid');
            $table->string('kind', 32);
            $table->string('direction', 8);
            $table->string('status', 16)->default('queued');
            $table->timestamp('requested_at');
            $table->timestamp('finished_at')->nullable();
            $table->index(['enterprise_id', 'application_id', 'status', 'direction', 'kind'], 'fulfillment_retry_worker');
            $table->index(['enterprise_id', 'message_uuid']);
            $table->index(['enterprise_id', 'correlation_uuid']);
        });

        Schema::table('platform_business_messages', function (Blueprint $table): void {
            $table->index(['enterprise_id', 'status', 'created_at'], 'fulfillment_message_queue');
            $table->index(['enterprise_id', 'correlation_uuid', 'kind'], 'fulfillment_message_trace');
        });
    }

    public function down(): void
    {
        Schema::table('platform_business_messages', function (Blueprint $table): void {
            $table->dropIndex('fulfillment_message_queue');
            $table->dropIndex('fulfillment_message_trace');
        });
        Schema::dropIfExists('platform_fulfillment_retries');
        Schema::dropIfExists('platform_fulfillment_checks');
        Schema::dropIfExists('platform_fulfillment_failures');
        Schema::dropIfExists('platform_fulfillment_operators');
    }
};
