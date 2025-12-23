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
            $table->string('action'); // e.g., 'task_created', 'task_revoked', 'access_validated'
            $table->string('entity_type'); // e.g., 'task', 'access_request'
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('details')->nullable(); // Additional context
            $table->string('ip_address')->nullable();
            $table->boolean('success')->default(true);
            $table->text('reason')->nullable(); // For access denials
            $table->timestamps();

            // Index for faster queries
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
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
