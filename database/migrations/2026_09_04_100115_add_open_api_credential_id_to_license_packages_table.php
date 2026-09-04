<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_packages', function (Blueprint $table): void {
            $table->foreignId('open_api_credential_id')
                ->nullable()
                ->after('project')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('license_packages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('open_api_credential_id');
        });
    }
};
