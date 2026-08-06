<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_packages', function (Blueprint $table): void {
            $table->timestamp('heartbeat_abnormal_notified_at')->nullable()->index()->after('last_verified_at');
        });

        Schema::create('license_instances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('open_api_credential_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('instance_id');
            $table->string('domain', 253);
            $table->string('project', 100)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('first_verified_at')->nullable();
            $table->timestamp('last_verified_at')->nullable()->index();
            $table->ipAddress('last_ip')->nullable();
            $table->timestamp('abnormal_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['license_package_id', 'instance_id'], 'license_instances_package_instance_unique');
        });

        Schema::create('license_verification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('license_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('license_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('open_api_credential_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requested_license_id', 80)->index();
            $table->uuid('instance_id');
            $table->string('domain', 253);
            $table->string('project', 100)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->string('result', 20)->index();
            $table->string('reason', 50)->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->timestamp('verified_at')->nullable()->index();
            $table->timestamps();

            $table->index(['license_package_id', 'verified_at']);
            $table->index(['instance_id', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_verification_logs');
        Schema::dropIfExists('license_instances');
        Schema::table('license_packages', function (Blueprint $table): void {
            $table->dropColumn('heartbeat_abnormal_notified_at');
        });
    }
};
