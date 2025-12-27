<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // DCFM and SOC can view user list
        return $user->isDcfm() || $user->isSoc();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view themselves, DCFM and SOC can view anyone
        return $user->id === $model->id || $user->isDcfm() || $user->isSoc();
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Only DCFM can create users
        return $user->isDcfm();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves, DCFM can update anyone
        return $user->id === $model->id || $user->isDcfm();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only DCFM can delete users, but not themselves
        return $user->isDcfm() && $user->id !== $model->id;
    }
}
