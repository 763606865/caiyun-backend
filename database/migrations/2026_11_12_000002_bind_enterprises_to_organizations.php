<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('enterprise_id')->nullable()->change();
        });
        Schema::table('platform_enterprises', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->unique()->constrained('platform_organizations')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_enterprises', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropUnique(['organization_id']);
            $table->dropColumn('organization_id');
        });
        // 独立组织没有所属企业，保留 enterprise_id 可空以避免回滚时损坏已有数据。
    }
};
