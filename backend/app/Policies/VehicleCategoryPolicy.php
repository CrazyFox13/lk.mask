<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\VehicleCategory $vehicleCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, VehicleCategory $vehicleCategory)
    {
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param VehicleCategory $vehicleCategory
     * @return bool
     */
    public function update(User $user, VehicleCategory $vehicleCategory): bool
    {
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param VehicleCategory $vehicleCategory
     * @return bool
     */
    public function delete(User $user, VehicleCategory $vehicleCategory): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\VehicleCategory $vehicleCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, VehicleCategory $vehicleCategory)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\VehicleCategory $vehicleCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, VehicleCategory $vehicleCategory)
    {
        //
    }
}
