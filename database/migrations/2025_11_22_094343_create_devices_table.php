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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // light, lock, thermostat, camera, sensor, etc.
            $table->string('location'); // living_room, bedroom, front_door, etc.
            $table->string('status')->default('off'); // on, off, locked, unlocked, etc.
            $table->json('settings')->nullable(); // Device-specific settings (brightness, temperature, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('min_role_hierarchy')->default(1); // Minimum role hierarchy required to access
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
