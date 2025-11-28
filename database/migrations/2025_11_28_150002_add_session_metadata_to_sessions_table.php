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
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('device_fingerprint')->nullable()->after('user_agent');
            $table->timestamp('last_activity_at')->nullable()->after('last_activity');
            $table->boolean('is_mobile')->default(false)->after('device_fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['device_fingerprint', 'last_activity_at', 'is_mobile']);
        });
    }
};

