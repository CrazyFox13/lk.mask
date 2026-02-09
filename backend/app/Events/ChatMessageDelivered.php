<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageDelivered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;

    public int $messageId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $chatId, int $messageId)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    /**
     * @return array|Channel[]|string[]
     */
    public function broadcastOn()
    {
        return [
            new PresenceChannel("chat-$this->chatId"),
            new PrivateChannel("chat-$this->chatId"),
        ];
    }
}
