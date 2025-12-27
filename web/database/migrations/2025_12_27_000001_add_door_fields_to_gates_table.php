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
        Schema::table('gates', function (Blueprint $table) {
            // Physical door identifier - matches client DEVICE_ID
            $table->string('door_id')->nullable()->unique()->after('gate_id');
            
            // IP address of the solenoid IoT device
            $table->string('door_ip_address')->nullable()->after('door_id');
            
            // Integration status with physical door
            $table->enum('integration_status', ['not_integrated', 'integrated', 'offline'])
                  ->default('not_integrated')
                  ->after('door_ip_address');
            
            // Last heartbeat from the IoT device
            $table->timestamp('last_heartbeat_at')->nullable()->after('integration_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gates', function (Blueprint $table) {
            $table->dropColumn(['door_id', 'door_ip_address', 'integration_status', 'last_heartbeat_at']);
        });
    }
};
