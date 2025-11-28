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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('severity')->default('info');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->text('encrypted_payload')->nullable(); // For encrypted sensitive data
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_type', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
