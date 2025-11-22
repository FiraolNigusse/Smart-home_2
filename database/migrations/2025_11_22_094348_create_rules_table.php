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
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade'); // If null, applies to all roles
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('cascade'); // If null, applies to all devices
            $table->string('action')->nullable(); // unlock, lock, turn_on, turn_off, etc. If null, applies to all actions
            $table->string('condition_type'); // time_window, day_of_week, device_state, etc.
            $table->json('condition_params'); // JSON with condition parameters (e.g., {"start_time": "22:00", "end_time": "06:00"})
            $table->boolean('is_active')->default(true);
            $table->string('effect'); // allow, deny
            $table->text('denial_message')->nullable(); // Message shown when rule denies access
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
