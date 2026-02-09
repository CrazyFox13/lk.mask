<?php

namespace App\Jobs\Chat;

use App\Jobs\SendPush;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class NewChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    public Message $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Message $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $allBadges = $this->user->countBadges();
        $text = $this->message->text;
        $device = $this->user->device;
        if (!$device) return;

        $order = $this->message->chat->order;
        dispatch(new SendPush(device: $device, title: $order->title, text: strip_tags($text), action: "new_chat_message", data: [
            'key' => "new_chat_message",
            'chat_id' => $this->message->chat_id,
        ], badgesCount: $allBadges['total_count']));
    }
}
