<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;

    public array $messagesId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $chatId, array $messagesId)
    {
        $this->chatId = $chatId;
        $this->messagesId = $messagesId;
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
