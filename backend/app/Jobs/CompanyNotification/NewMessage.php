<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewMessage extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'У вас новое сообщение';
    public string $text = '';
    public string $key = 'new_message';
    public string $resource_key = 'chat_id';
    public int $resource_id;
    public array $recipients=[];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        parent::__construct();

        $chat = $message->chat;
        $this->resource_id = $chat->id;
        $reportId = $chat->report_id;
        $this->additional_data = [
            'report_id' => $reportId,
        ];
        $this->text = "В претензии <b>№$reportId</b> появилось новое сообщение";

        $this->recipients = $chat->users()
            ->where("users.id", "!=", $message->author_id)
            ->whereHas("notificationTypePivots", function ($q) {
                $q->push()->active()->whereHas("type", function ($q) {
                    $q->where("key", "personal");
                });
            })
            ->get()
            ->map(function (User $user) {
                return [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id
                ];
            })->toArray();
    }
}
