<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleGroupPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user):bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user):bool
    {
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param VehicleGroup $vehicleGroup
     * @return bool
     */
    public function update(User $user, VehicleGroup $vehicleGroup):bool
    {
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param VehicleGroup $vehicleGroup
     * @return bool
     */
    public function delete(User $user, VehicleGroup $vehicleGroup):bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VehicleGroup  $vehicleGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, VehicleGroup $vehicleGroup)
    {
        //
    }
}
