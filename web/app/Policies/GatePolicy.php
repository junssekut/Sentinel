<?php

namespace App\Policies;

use App\Models\Gate;
use App\Models\User;

class GatePolicy
{
    /**
     * Determine whether the user can view any gates.
     */
    public function viewAny(User $user): bool
    {
        // DCFM and SOC can view gates
        return $user->isDcfm() || $user->isSoc();
    }

    /**
     * Determine whether the user can view the gate.
     */
    public function view(User $user, Gate $gate): bool
    {
        // DCFM and SOC can view gates
        return $user->isDcfm() || $user->isSoc();
    }

    /**
     * Determine whether the user can create gates.
     */
    public function create(User $user): bool
    {
        // Only SOC can create gates
        return $user->isSoc();
    }

    /**
     * Determine whether the user can update the gate.
     * Only SOC can update gates (including door bindings).
     */
    public function update(User $user, Gate $gate): bool
    {
        return $user->isSoc();
    }

    /**
     * Determine whether the user can delete the gate.
     */
    public function delete(User $user, Gate $gate): bool
    {
        // Only SOC can delete gates
        return $user->isSoc();
    }
}
