<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_contract_approvals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('enterprise_id');
            $table->uuid('enterprise_uuid');
            $table->unsignedBigInteger('source_application_id');
            $table->unsignedBigInteger('target_application_id');
            $table->uuid('contract_uuid');
            $table->unsignedInteger('revision');
            $table->uuid('applicant_user_uuid');
            $table->uuid('applicant_employment_uuid');
            $table->longText('snapshot_json');
            $table->string('snapshot_hash', 64);
            $table->string('status', 20)->default('queued');
            $table->uuid('oa_contract_uuid')->nullable();
            $table->uuid('oa_instance_uuid')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['enterprise_id', 'source_application_id', 'contract_uuid', 'revision'], 'platform_contract_revision_unique');
            $table->index(['enterprise_id', 'target_application_id', 'id'], 'platform_contract_receiver_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_contract_approvals');
    }
};
