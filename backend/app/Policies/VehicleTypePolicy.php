<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleTypePolicy
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
     * @param User $user
     * @param VehicleType $vehicleType
     * @return bool
     */
    public function view(User $user, VehicleType $vehicleType):bool
    {
        return true;
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
     * @param VehicleType $vehicleType
     * @return bool
     */
    public function update(User $user, VehicleType $vehicleType):bool
    {
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param VehicleType $vehicleType
     * @return bool
     */
    public function delete(User $user, VehicleType $vehicleType):bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VehicleType  $vehicleType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, VehicleType $vehicleType)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VehicleType  $vehicleType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, VehicleType $vehicleType)
    {
        //
    }
}
