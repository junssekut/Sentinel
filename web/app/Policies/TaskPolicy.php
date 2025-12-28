<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tasks, but filtered by role
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // DCFM and SOC can view all tasks
        if ($user->canViewAllTasks()) {
            return true;
        }

        // Vendors can only view their own tasks
        return $user->isVendor() && $task->vendors->contains('id', $user->id);
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        // Only DCFM can create tasks
        return $user->isDcfm();
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // Only DCFM can update tasks
        return $user->isDcfm();
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Only DCFM can delete tasks
        return $user->isDcfm();
    }

    /**
     * Determine whether the user can revoke the task.
     */
    public function revoke(User $user, Task $task): bool
    {
        // Only DCFM can revoke tasks
        return $user->isDcfm();
    }

    /**
     * Determine whether the user can complete the task.
     */
    public function complete(User $user, Task $task): bool
    {
        // Only DCFM can complete tasks
        return $user->isDcfm();
    }
}
