<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Auth\Access\Response;

class SpacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Space $space): bool
    {
        return $user->id === $space->venue->owner_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models (for a specific venue).
     */
    public function create(User $user, Venue $venue): bool
    {
        return $user->id === $venue->owner_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Space $space): bool
    {
        return $user->id === $space->venue->owner_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Space $space): bool
    {
        return $user->id === $space->venue->owner_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Space $space): bool
    {
        return $user->id === $space->venue->owner_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Space $space): bool
    {
        return $user->id === $space->venue->owner_id || $user->isAdmin();
    }
}
