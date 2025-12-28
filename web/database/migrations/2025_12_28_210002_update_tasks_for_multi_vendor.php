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
        Schema::table('tasks', function (Blueprint $table) {
            // Rename notes to title
            $table->renameColumn('notes', 'title');
            
            // Drop vendor_id foreign key and column
            $table->dropForeign(['vendor_id']);
            $table->dropIndex('tasks_vendor_id_status_index');
            $table->dropColumn('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add vendor_id back
            $table->foreignId('vendor_id')->after('id')->constrained('users')->onDelete('cascade');
            $table->index(['vendor_id', 'status'], 'tasks_vendor_id_status_index');
            
            // Rename title back to notes
            $table->renameColumn('title', 'notes');
        });
    }
};
