<?php

namespace App\Events;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;

    public int $messageId;

    public int $authorId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $chatId, int $messageId, int $authorId)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->authorId = $authorId;
    }

    /**
     * @return array|Channel[]|string[]
     */
    public function broadcastOn()
    {
        $message = Message::query()->find($this->messageId);
        $message->delivered_at = Carbon::now();
        $message->save();

        broadcast(new ChatMessageDelivered($this->chatId, $this->messageId))->toOthers();

        return [
            new PresenceChannel("chat-$this->chatId"),
            new PrivateChannel("chat-$this->chatId"),
        ];
    }
}
