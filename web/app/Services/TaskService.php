<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Gate;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskService
{
    /**
     * Create a new task with validation.
     *
     * @param array $data Task data
     * @param User $creator The user creating the task
     * @return array{success: bool, task?: Task, error?: string}
     */
    public function createTask(array $data, User $creator): array
    {
        // Validate vendors
        $vendorIds = $data['vendor_ids'];
        $vendors = User::whereIn('id', $vendorIds)->where('role', 'vendor')->get();
        if ($vendors->count() !== count($vendorIds)) {
            return ['success' => false, 'error' => 'One or more invalid vendors specified'];
        }

        // Validate PIC (must not be a vendor)
        $pic = User::find($data['pic_id']);
        if (!$pic || $pic->isVendor()) {
            return ['success' => false, 'error' => 'Invalid PIC specified'];
        }

        // Validate time window
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime->lte($startTime)) {
            return ['success' => false, 'error' => 'End time must be after start time'];
        }

        // Validate gates
        $gateIds = $data['gate_ids'] ?? [];
        if (empty($gateIds)) {
            return ['success' => false, 'error' => 'At least one gate must be specified'];
        }

        $gates = Gate::whereIn('id', $gateIds)->active()->get();
        if ($gates->count() !== count($gateIds)) {
            return ['success' => false, 'error' => 'One or more invalid gates specified'];
        }

        try {
            DB::beginTransaction();

            // Create the task
            $task = Task::create([
                'title' => $data['title'],
                'pic_id' => $data['pic_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'active',
                'created_by' => $creator->id,
            ]);

            // Attach vendors
            $task->vendors()->attach($vendorIds);

            // Attach gates
            $task->gates()->attach($gateIds);

            // Log the creation
            AuditLog::logTaskCreated($task, $creator->id, request()->ip());

            DB::commit();

            return ['success' => true, 'task' => $task->load(['vendors', 'pic', 'gates'])];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Failed to create task: ' . $e->getMessage()];
        }
    }

    /**
     * Revoke a task.
     *
     * @param Task $task The task to revoke
     * @param User $revoker The user revoking the task
     * @param string|null $reason Optional reason for revocation
     * @return array{success: bool, error?: string}
     */
    public function revokeTask(Task $task, User $revoker, ?string $reason = null): array
    {
        if ($task->status !== 'active') {
            return ['success' => false, 'error' => 'Only active tasks can be revoked'];
        }

        try {
            $task->markAsRevoked();

            // Log the revocation
            AuditLog::logTaskRevoked($task, $revoker->id, request()->ip(), $reason);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to revoke task: ' . $e->getMessage()];
        }
    }

    /**
     * Complete a task.
     *
     * @param Task $task The task to complete
     * @return array{success: bool, error?: string}
     */
    public function completeTask(Task $task): array
    {
        if ($task->status !== 'active') {
            return ['success' => false, 'error' => 'Only active tasks can be completed'];
        }

        try {
            $task->markAsCompleted();
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to complete task: ' . $e->getMessage()];
        }
    }

    /**
     * Get tasks visible to a user based on their role.
     *
     * @param User $user The user requesting tasks
     * @param array $filters Optional filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasksForUser(User $user, array $filters = [])
    {
        $query = Task::with(['vendors', 'pic', 'gates']);

        // Apply role-based visibility
        if ($user->isVendor()) {
            // Vendors can only see tasks they are assigned to
            $query->whereHas('vendors', fn($q) => $q->where('users.id', $user->id));
        }
        // DCFM and SOC can see all tasks (no filter needed)

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply date filters
        if (!empty($filters['from_date'])) {
            $query->where('start_time', '>=', Carbon::parse($filters['from_date']));
        }
        if (!empty($filters['to_date'])) {
            $query->where('end_time', '<=', Carbon::parse($filters['to_date']));
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
