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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->string('action'); // unlock, lock, turn_on, turn_off, view, etc.
            $table->string('status'); // allowed, denied
            $table->text('message')->nullable(); // Additional details or denial reason
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional context (device state before/after, rule that triggered, etc.)
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['user_id', 'performed_at']);
            $table->index(['device_id', 'performed_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
