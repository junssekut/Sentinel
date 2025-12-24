<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Gate;
use App\Models\Task;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create DCFM (Admin)
        $dcfm = User::create([
            'name' => 'John Admin',
            'email' => 'admin@sentinel.com',
            'password' => 'password',
            'role' => 'dcfm',
            'face_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        // Create SOC (Security)
        $soc = User::create([
            'name' => 'Sarah Security',
            'email' => 'soc@sentinel.com',
            'password' => 'password',
            'role' => 'soc',
            'face_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        // Create Vendors
        $vendor1 = User::create([
            'name' => 'Mike Vendor',
            'email' => 'vendor1@example.com',
            'password' => 'password',
            'role' => 'vendor',
            'face_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $vendor2 = User::create([
            'name' => 'Lisa Vendor',
            'email' => 'vendor2@example.com',
            'password' => 'password',
            'role' => 'vendor',
            'face_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        // Create Gates
        $gate1 = Gate::create([
            'name' => 'Main Entrance',
            'location' => 'Building A, Ground Floor',
            'description' => 'Primary entrance for all visitors',
            'gate_id' => 'GATE-MAIN-001',
            'is_active' => true,
        ]);

        $gate2 = Gate::create([
            'name' => 'Server Room A',
            'location' => 'Building A, Floor 2',
            'description' => 'High security server room',
            'gate_id' => 'GATE-SRVA-002',
            'is_active' => true,
        ]);

        $gate3 = Gate::create([
            'name' => 'Server Room B',
            'location' => 'Building B, Floor 1',
            'description' => 'Secondary server room',
            'gate_id' => 'GATE-SRVB-003',
            'is_active' => true,
        ]);

        $gate4 = Gate::create([
            'name' => 'Network Operations Center',
            'location' => 'Building A, Floor 3',
            'description' => 'NOC monitoring room',
            'gate_id' => 'GATE-NOC-004',
            'is_active' => true,
        ]);

        // Create Tasks
        $task1 = Task::create([
            'vendor_id' => $vendor1->id,
            'pic_id' => $dcfm->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(4),
            'status' => 'active',
            'notes' => 'Hardware maintenance for Server Room A',
            'created_by' => $dcfm->id,
        ]);
        $task1->gates()->attach([$gate1->id, $gate2->id]);

        $task2 = Task::create([
            'vendor_id' => $vendor2->id,
            'pic_id' => $soc->id,
            'start_time' => Carbon::now()->addHours(2),
            'end_time' => Carbon::now()->addHours(6),
            'status' => 'active',
            'notes' => 'Network equipment inspection',
            'created_by' => $dcfm->id,
        ]);
        $task2->gates()->attach([$gate1->id, $gate4->id]);

        // Create a completed task
        $task3 = Task::create([
            'vendor_id' => $vendor1->id,
            'pic_id' => $soc->id,
            'start_time' => Carbon::yesterday()->setHour(9),
            'end_time' => Carbon::yesterday()->setHour(17),
            'status' => 'completed',
            'notes' => 'Routine maintenance completed',
            'created_by' => $dcfm->id,
        ]);
        $task3->gates()->attach([$gate1->id, $gate3->id]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Test Accounts:');
        $this->command->info('  DCFM: admin@sentinel.com / password');
        $this->command->info('  SOC:  soc@sentinel.com / password');
        $this->command->info('  Vendor: vendor1@example.com / password');
    }
}
