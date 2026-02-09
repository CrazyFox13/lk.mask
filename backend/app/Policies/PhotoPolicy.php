<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Photo;
use App\Models\PhotoGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoPolicy
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
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Photo $photo)
    {
        //
    }

    /**
     * @param User $user
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @return bool
     */
    public function create(User $user,Company $company, PhotoGroup $photoGroup):bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        if ($company->id !== $user->company_id) return false;

        return $company->id === $photoGroup->company_id;
    }

    /**
     * @param User $user
     * @param Photo $photo
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @return bool
     */
    public function update(User $user, Photo $photo, Company $company, PhotoGroup $photoGroup):bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        if ($company->id !== $user->company_id) return false;

        if ($company->id !== $photoGroup->company_id) return false;

        return $photo->photo_group_id === $photoGroup->id;
    }

    /**
     * @param User $user
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @param Photo $photo
     * @return bool
     */
    public function delete(User $user, Photo $photo, Company $company, PhotoGroup $photoGroup): bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;

        if ($company->id !== $user->company_id) return false;

        if ($company->id !== $photoGroup->company_id) return false;

        return $photo->photo_group_id === $photoGroup->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Photo $photo)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Photo $photo)
    {
        //
    }
}
