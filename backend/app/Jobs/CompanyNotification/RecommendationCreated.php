<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\NotificationType;
use App\Models\NotificationTypeUser;
use App\Models\Recommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecommendationCreated extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'У вас новая рекомендация';
    public string $text = 'Вас рекомендуют. Ознакомьтесь с новой рекомендацией в личном кабинете';
    public string $key = 'recommendation_created';
    public string $resource_key = 'recommendation_id';
    public int $resource_id;
    public array $recipients=[];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Recommendation $recommendation)
    {
        parent::__construct();

        $this->resource_id = $recommendation->id;

        //if (!NotificationTypeUser::isEnabled($recommendation->target_user_id, 'orders', 'push')) return;
        if (!NotificationTypeUser::isEnabled($recommendation->target_user_id, 'personal', 'push')) return;

        $this->recipients = [[
            'user_id' => $recommendation->target_user_id,
            'company_id' => $recommendation->company_id
        ]];

        $author = $recommendation->author;
        $senderName = "$author->name $author->surname";
        if ($company = $author->company) {
            $senderName = $company->title;
        }
        $this->text = "Вас рекомендует <b>$senderName</b>, подробнее в рекомендациях";
    }
}
