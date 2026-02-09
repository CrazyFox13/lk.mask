<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $uids;
    public int $chat_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $uids, int $chat_id)
    {
        $this->uids = $uids;
        $this->chat_id = $chat_id;
    }

    /**
     * @return array|Channel[]|string[]
     */
    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->uids as $uid) {
            $channels[] = new PrivateChannel("user-$uid");
            $channels[] = new PresenceChannel("user-$uid");
        }
        return $channels;
    }
}
