<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('sensitivity_level_id')
                ->nullable()
                ->after('hierarchy')
                ->constrained('sensitivity_levels')
                ->nullOnDelete();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('sensitivity_level_id')
                ->nullable()
                ->after('min_role_hierarchy')
                ->constrained('sensitivity_levels')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['sensitivity_level_id']);
            $table->dropColumn('sensitivity_level_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['sensitivity_level_id']);
            $table->dropColumn('sensitivity_level_id');
        });
    }
};
