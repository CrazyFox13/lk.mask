<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use \Illuminate\Auth\Access\Response;

class OrderOfferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param Order $order
     * @return Response|bool
     */
    public function viewAny(User $user, Order $order): Response|bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $user->company_id === $order->company_id;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param OrderOffer $orderOffer
     * @return Response|bool
     */
    public function view(User $user, OrderOffer $orderOffer): Response|bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $user->company_id === $orderOffer->company_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param Order $order
     * @return Response|bool
     */
    public function create(User $user, Order $order): Response|bool
    {
        $offerExists = $order->offers()->where("company_id", "=", $user->company_id)->exists();
        return !$offerExists;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param OrderOffer $orderOffer
     * @return Response|bool
     */
    public function update(User $user, OrderOffer $orderOffer): Response|bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $user->company_id === $orderOffer->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param OrderOffer $orderOffer
     * @return Response|bool
     */
    public function delete(User $user, OrderOffer $orderOffer): Response|bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $user->company_id === $orderOffer->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param OrderOffer $orderOffer
     * @return Response|bool
     */
    public function restore(User $user, OrderOffer $orderOffer)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param OrderOffer $orderOffer
     * @return Response|bool
     */
    public function forceDelete(User $user, OrderOffer $orderOffer)
    {
        //
    }
}
