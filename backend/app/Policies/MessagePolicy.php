<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;

class MessagePolicy
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
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function view(User $user, Message $message): bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;
        return DB::table("chat_user")
            ->where('user_id', $user->id)
            ->where('chat_id', $message->chat_id)
            ->exists();

    }

    /**
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function create(User $user, Chat $chat): bool
    {
        if ($user->isAdmin() || $user->isModerator()) return true;
        $pivot = DB::table("chat_user")
            ->where('user_id', $user->id)
            ->where('chat_id', $chat->id)
            ->first();
        return $pivot && !$pivot->blocked_at;
    }

    /**
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function update(User $user, Message $message): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $message->author_id === $user->id;
    }

    /**
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function delete(User $user, Message $message): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isModerator()) return true;
        return $message->author_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Message $message
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Message $message)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Message $message
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Message $message)
    {
        //
    }
}
