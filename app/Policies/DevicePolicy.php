<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DevicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view devices (filtered by hierarchy in controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Device $device): bool
    {
        // Check if user has minimum hierarchy
        $hierarchy = $user->role->hierarchy ?? 0;
        return $device->isAccessibleBy($hierarchy);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only owners can create devices
        return $user->isOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Device $device): bool
    {
        // Only owners can update devices
        return $user->isOwner();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Device $device): bool
    {
        // Only owners can delete devices
        return $user->isOwner();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Device $device): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Device $device): bool
    {
        return false;
    }
}
