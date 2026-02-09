<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\PhotoGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhotoGroup  $photoGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, PhotoGroup $photoGroup)
    {
        //
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function create(User $user,Company $company):bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        return $user->company_id === $company->id;
    }

    /**
     * @param User $user
     * @param PhotoGroup $photoGroup
     * @return bool
     */
    public function update(User $user, PhotoGroup $photoGroup,Company $company):bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        return $user->company_id === $photoGroup->company_id;
    }

    /**
     * @param User $user
     * @param PhotoGroup $photoGroup
     * @return bool
     */
    public function delete(User $user, PhotoGroup $photoGroup):bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        return $user->company_id === $photoGroup->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhotoGroup  $photoGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, PhotoGroup $photoGroup)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhotoGroup  $photoGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, PhotoGroup $photoGroup)
    {
        //
    }
}
