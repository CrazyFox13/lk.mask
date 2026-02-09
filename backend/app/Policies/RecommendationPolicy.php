<?php

namespace App\Policies;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecommendationPolicy
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
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Recommendation $recommendation)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Recommendation $recommendation)
    {
        if ($user->isAdmin() || $user->isModerator()) return true;
        return $recommendation->author_user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Recommendation $recommendation)
    {
        if ($user->isAdmin() || $user->isModerator()) return true;
        if ($recommendation->author_user_id === $user->id) return true;
        return $recommendation->target_user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Recommendation $recommendation)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Recommendation $recommendation)
    {
        //
    }

    public function moderate(User $user, Recommendation $recommendation)
    {
        return $user->isAdmin() || $user->isModerator();
    }
}
