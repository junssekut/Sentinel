<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Gate;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();
        $pics = User::whereIn('role', ['dcfm', 'soc'])->get();
        $gates = Gate::all();

        if ($vendors->isEmpty() || $pics->isEmpty()) {
            $this->command->warn('No vendors or PICs found. Skipping TaskSeeder.');
            return;
        }

        // Specific scenarios requested:
        // 1. Vendor Arjuna alone with DCFM/SOC (PIC) Matthew Raditya
        // 2. Vendor Arjuna and Vendor Jelvis with PIC Felix Febryan Wana
        
        $vendorArjuna = $vendors->firstWhere('email', 'arjuna.andio@binus.ac.id');
        $vendorJelvis = $vendors->firstWhere('email', 'jelvis.anggono@binus.ac.id') ?? $vendors->firstWhere('name', 'like', '%Jelvis%'); // Fallback if email varies
        
        $picMatthew = $pics->firstWhere('email', 'matthew.raditya@binus.ac.id');
        $picFelix = $pics->firstWhere('email', 'felix.wana@binus.ac.id');

        // Task 1: Arjuna + Matthew
        if ($vendorArjuna && $picMatthew) {
            $task1 = Task::create([
                'vendor_id' => $vendorArjuna->id,
                'pic_id' => $picMatthew->id,
                'start_time' => Carbon::now()->addHours(2),
                'end_time' => Carbon::now()->addHours(6),
                'status' => 'active',
                'notes' => 'Server Maintenance (Arjuna)',
                'created_by' => $picMatthew->id,
            ]);
            if ($gates->isNotEmpty()) $task1->gates()->attach($gates->random(1));
            $this->command->info("Created Task: {$task1->notes} | Vendor: {$vendorArjuna->name} | PIC: {$picMatthew->name}");
        }

        // Task 2: Arjuna + Jelvis + Felix
        // Note: Standard Task model usually has one vendor_id. If multiple vendors are needed, 
        // implies Many-to-Many relationship or main vendor + additional data. 
        // Based on typical schema (vendor_id FK), we can only assign one main vendor.
        // I will assign Arjuna as main, and mention Jelvis in notes or if there is a pivots table for multiple vendors (unlikely in standard request unless specified).
        // User asked: "vendor arjuna and vendor jelvis with pic felix".
        // I will create the task for Arjuna (main) and note Jelvis. If schema supported it, I'd attach both.
        // Assuming single vendor per task for now based on previous schema inspection.
        
        // Task 2: Arjuna + Jelvis + Felix (existing code)
        if ($vendorArjuna && $picFelix) {
             $task2 = Task::create([
                'vendor_id' => $vendorArjuna->id,
                'pic_id' => $picFelix->id,
                'start_time' => Carbon::tomorrow()->setHour(9),
                'end_time' => Carbon::tomorrow()->setHour(17),
                'status' => 'active',
                'notes' => 'Joint Inspection (Arjuna & Jelvis)',
                'created_by' => $picFelix->id,
            ]);
            if ($gates->isNotEmpty()) $task2->gates()->attach($gates->pluck('id'));
            $this->command->info("Created Task: {$task2->notes} | Vendor: {$vendorArjuna->name} (+Jelvis) | PIC: {$picFelix->name}");
        }

        // Task 3: Jonathan + Matthew
        $vendorJonathan = $vendors->firstWhere('email', 'jonathan.chandra@binus.ac.id') ?? $vendors->firstWhere('name', 'like', '%Jonathan%');
        
        if ($vendorJonathan && $picMatthew) {
            $task3 = Task::create([
                'vendor_id' => $vendorJonathan->id,
                'pic_id' => $picMatthew->id,
                'start_time' => Carbon::now()->addDays(2)->setHour(10),
                'end_time' => Carbon::now()->addDays(2)->setHour(14),
                'status' => 'active',
                'notes' => 'Network Upgrade (Jonathan)',
                'created_by' => $picMatthew->id,
            ]);
            if ($gates->isNotEmpty()) $task3->gates()->attach($gates->random(1));
            $this->command->info("Created Task: {$task3->notes} | Vendor: {$vendorJonathan->name} | PIC: {$picMatthew->name}");
        }
        
        // Create random tasks for others if any
        $otherVendors = $vendors->whereNotIn('id', [$vendorArjuna->id ?? 0]);
        if ($otherVendors->isNotEmpty()) {
             foreach($otherVendors as $vendor) {
                 $pic = $pics->random();
                 $task = Task::create([
                    'vendor_id' => $vendor->id,
                    'pic_id' => $pic->id,
                    'start_time' => Carbon::now()->subDays(rand(1,5)),
                    'end_time' => Carbon::now()->subDays(rand(1,5))->addHours(4),
                    'status' => 'completed',
                    'notes' => 'Past Routine Check',
                    'created_by' => $pic->id,
                ]);
                $this->command->info("Created Random Task: {$task->notes}");
             }
        }
    }
}
