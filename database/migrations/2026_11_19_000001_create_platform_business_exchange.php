<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_master_entities', function (Blueprint $t): void {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->unsignedBigInteger('enterprise_id');
            $t->string('kind', 30);
            $t->unsignedBigInteger('source_application_id');
            $t->string('source_id', 100);
            $t->unsignedInteger('version');
            $t->json('payload');
            $t->string('payload_hash', 64);
            $t->timestamps();
            $t->unique(['enterprise_id', 'kind', 'source_application_id', 'source_id'], 'platform_master_source_unique');
            $t->index(['enterprise_id', 'kind', 'id']);
        });
        Schema::create('platform_master_bindings', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('enterprise_id');
            $t->unsignedBigInteger('application_id');
            $t->string('kind', 30);
            $t->uuid('entity_uuid');
            $t->string('local_id', 100);
            $t->timestamps();
            $t->unique(['application_id', 'enterprise_id', 'kind', 'local_id'], 'platform_master_local_unique');
            $t->unique(['application_id', 'enterprise_id', 'entity_uuid'], 'platform_master_binding_unique');
        });
        Schema::create('platform_business_messages', function (Blueprint $t): void {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->unsignedBigInteger('enterprise_id');
            $t->uuid('enterprise_uuid');
            $t->unsignedBigInteger('source_application_id');
            $t->unsignedBigInteger('target_application_id');
            $t->string('kind', 50);
            $t->uuid('correlation_uuid');
            $t->longText('payload_json');
            $t->string('payload_hash', 64);
            $t->string('status', 20)->default('queued');
            $t->json('result')->nullable();
            $t->timestamps();
            $t->index(['target_application_id', 'enterprise_id', 'status', 'id'], 'platform_message_inbox_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_business_messages');
        Schema::dropIfExists('platform_master_bindings');
        Schema::dropIfExists('platform_master_entities');
    }
};
