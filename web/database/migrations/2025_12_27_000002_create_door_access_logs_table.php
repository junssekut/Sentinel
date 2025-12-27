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
        Schema::create('door_access_logs', function (Blueprint $table) {
            $table->id();
            
            // Gate reference
            $table->foreignId('gate_id')->constrained('gates')->onDelete('cascade');
            
            // Task that authorized this access (nullable if access was denied)
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('set null');
            
            // Vendor who accessed
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('set null');
            
            // PIC who approved the access
            $table->foreignId('pic_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Event type: entry (door opened), exit (session completed), denied (access refused)
            $table->enum('event_type', ['entry', 'exit', 'denied'])->default('entry');
            
            // Reason for denial if applicable
            $table->string('reason')->nullable();
            
            // Additional details as JSON
            $table->json('details')->nullable();
            
            // Session ID from the access session
            $table->string('session_id')->nullable();
            
            // IP address of the client device
            $table->string('client_ip')->nullable();
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['gate_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('door_access_logs');
    }
};
