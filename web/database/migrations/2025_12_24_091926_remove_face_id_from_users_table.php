<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to drop the unique index first
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support DROP INDEX directly in Schema, use raw SQL
            DB::statement('DROP INDEX IF EXISTS users_face_id_unique');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['face_id']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('face_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('face_id')->unique()->nullable()->after('face_image');
        });
    }
};
