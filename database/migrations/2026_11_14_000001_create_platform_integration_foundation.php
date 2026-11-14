<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_applications', function (Blueprint $table): void {
            $table->json('redirect_uris')->nullable()->comment('登录精确回跳白名单');
        });
        Schema::table('platform_employees', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->comment('稳定任职标识');
            $table->foreignId('organization_id')->nullable()->constrained('platform_organizations')->restrictOnDelete();
        });
        DB::table('platform_employees')->orderBy('id')->each(function ($row): void {
            DB::table('platform_employees')->where('id', $row->id)->update(['uuid' => (string) Str::uuid7()]);
        });
        Schema::create('platform_application_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->unsignedBigInteger('enterprise_id')->default(0)->comment('0仅用于显式平台权限或建企引导');
            $table->string('scope', 80);
            $table->unique(['application_id', 'enterprise_id', 'scope'], 'platform_scope_unique');
        });
        Schema::create('platform_application_grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->foreignId('enterprise_id')->constrained('platform_enterprises');
            $table->unsignedBigInteger('employee_id')->default(0)->comment('0为企业开通，其余为任职授权');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['application_id', 'enterprise_id', 'employee_id'], 'platform_grant_unique');
        });
        Schema::create('platform_directory_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->unsignedBigInteger('enterprise_id')->default(0);
            $table->string('entity_type', 24);
            $table->string('source_id', 191);
            $table->uuid('entity_uuid');
            $table->unsignedBigInteger('source_version');
            $table->string('payload_hash', 64);
            $table->unique(['application_id', 'entity_type', 'source_id'], 'platform_source_unique');
        });
        Schema::create('platform_directory_changes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->string('entity_type', 24);
            $table->uuid('entity_uuid');
            $table->json('payload');
            $table->timestamp('created_at');
            $table->index(['enterprise_id', 'id']);
        });
        Schema::create('platform_integration_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->string('idempotency_key', 191);
            $table->string('request_hash', 64);
            $table->json('result');
            $table->unique(['application_id', 'idempotency_key'], 'platform_request_unique');
        });
        Schema::create('platform_directory_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('entity_type', 24);
            $table->unsignedBigInteger('cursor');
            $table->json('items');
            $table->timestamp('expires_at');
        });
        Schema::create('platform_login_tickets', function (Blueprint $table): void {
            $table->string('code_hash', 64)->primary();
            $table->foreignId('application_id')->constrained('platform_applications');
            $table->unsignedBigInteger('employee_id');
            $table->string('redirect_uri', 1000);
            $table->string('state', 191);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
        });
        Schema::create('platform_integration_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('application_id')->nullable();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('action', 100);
            $table->string('object_key', 191)->nullable();
            $table->string('trace_id', 100);
            $table->unsignedSmallInteger('status');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('platform_applications', fn (Blueprint $table) => $table->dropColumn('redirect_uris'));
        foreach (['platform_integration_audits', 'platform_login_tickets', 'platform_directory_snapshots', 'platform_integration_requests', 'platform_directory_changes', 'platform_directory_sources', 'platform_application_grants', 'platform_application_scopes'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('platform_employees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn('uuid');
        });
    }
};
