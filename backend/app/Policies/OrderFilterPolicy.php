<?php

namespace App\Policies;

use App\Models\OrderFilter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderFilterPolicy
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
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, OrderFilter $orderFilter)
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
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, OrderFilter $orderFilter)
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $user->id === $orderFilter->user_id;
    }

    /**
     * @param User $user
     * @param OrderFilter $orderFilter
     * @return bool
     */
    public function delete(User $user, OrderFilter $orderFilter)
    {
        if($user->isAdmin()) return true;
        if($user->isModerator()) return true;
        return $user->id === $orderFilter->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, OrderFilter $orderFilter)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, OrderFilter $orderFilter)
    {
        //
    }
}
