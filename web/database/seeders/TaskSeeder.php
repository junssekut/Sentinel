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

        $vendorArjuna = $vendors->firstWhere('email', 'arjuna.andio@binus.ac.id');
        $vendorJelvis = $vendors->firstWhere('email', 'jelvis.anggono@binus.ac.id');
        $vendorJonathan = $vendors->firstWhere('email', 'jonathan.chandra@binus.ac.id');
        
        $picMatthew = $pics->firstWhere('email', 'matthew.raditya@binus.ac.id');
        $picFelix = $pics->firstWhere('email', 'felix.wana@binus.ac.id');

        // Task 1: Server Maintenance - Arjuna only, PIC Matthew
        if ($vendorArjuna && $picMatthew) {
            $task1 = Task::create([
                'title' => 'Server Maintenance',
                'pic_id' => $picMatthew->id,
                'start_time' => Carbon::now()->subHour(),
                'end_time' => Carbon::now()->addHours(6),
                'status' => 'active',
                'created_by' => $picMatthew->id,
            ]);
            $task1->vendors()->attach([$vendorArjuna->id]);
            if ($gates->isNotEmpty()) $task1->gates()->attach($gates->random(1));
            $this->command->info("Created Task: {$task1->title} | Vendors: {$vendorArjuna->name} | PIC: {$picMatthew->name}");
        }

        // Task 2: Joint Inspection - Arjuna AND Jelvis, PIC Felix
        if ($vendorArjuna && $vendorJelvis && $picFelix) {
            $task2 = Task::create([
                'title' => 'Joint Inspection',
                'pic_id' => $picFelix->id,
                'start_time' => Carbon::tomorrow()->setHour(9),
                'end_time' => Carbon::tomorrow()->setHour(17),
                'status' => 'active',
                'created_by' => $picFelix->id,
            ]);
            $task2->vendors()->attach([$vendorArjuna->id, $vendorJelvis->id]);
            if ($gates->isNotEmpty()) $task2->gates()->attach($gates->pluck('id'));
            $this->command->info("Created Task: {$task2->title} | Vendors: {$vendorArjuna->name}, {$vendorJelvis->name} | PIC: {$picFelix->name}");
        }

        // Task 3: Network Upgrade - Jonathan only, PIC Matthew
        if ($vendorJonathan && $picMatthew) {
            $task3 = Task::create([
                'title' => 'Network Upgrade',
                'pic_id' => $picMatthew->id,
                'start_time' => Carbon::now()->addDays(2)->setHour(10),
                'end_time' => Carbon::now()->addDays(2)->setHour(14),
                'status' => 'active',
                'created_by' => $picMatthew->id,
            ]);
            $task3->vendors()->attach([$vendorJonathan->id]);
            if ($gates->isNotEmpty()) $task3->gates()->attach($gates->random(1));
            $this->command->info("Created Task: {$task3->title} | Vendors: {$vendorJonathan->name} | PIC: {$picMatthew->name}");
        }
    }
}
