<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gate;

class GateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gate::updateOrCreate(
            ['gate_id' => 'GATE-001'],
            [
                'name' => 'Main Entrance',
                'location' => 'Lobby',
                'description' => 'Primary entry point',
                'is_active' => true,
            ]
        );

        Gate::updateOrCreate(
            ['gate_id' => 'GATE-002'],
            [
                'name' => 'Back Entrance',
                'location' => 'Parking Lot',
                'description' => 'Staff entry point',
                'is_active' => true,
            ]
        );
    }
}
