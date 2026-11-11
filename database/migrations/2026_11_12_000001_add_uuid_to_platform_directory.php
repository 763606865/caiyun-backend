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
        foreach (['platform_enterprises', 'platform_organizations'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique();
            });

            DB::table($name)->select('id')->orderBy('id')->chunkById(500, function ($records) use ($name) {
                foreach ($records as $record) {
                    DB::table($name)->where('id', $record->id)->update(['uuid' => (string) Str::uuid7()]);
                }
            });

            Schema::table($name, function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['platform_organizations', 'platform_enterprises'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
