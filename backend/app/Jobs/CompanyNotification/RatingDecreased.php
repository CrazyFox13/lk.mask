<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\Company;
use App\Models\NotificationTypeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RatingDecreased extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Внимание! Ваш рейтинг упал!';
    public string $text = 'Ваш рейтинг уменьшился до _число рейтинг_. Постарайтесь его увеличить!';
    public string $key = 'rating_decreased';
    public string $resource_key = 'company_id';
    public int $resource_id;
    public array $recipients=[];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        parent::__construct();

        if (!NotificationTypeUser::isEnabled($company->boss->id, 'personal', 'push')) return;

        $this->resource_id = $company->id;

        $this->recipients = [[
            'user_id' => $company->boss->id,
            'company_id' => $company->id,
        ]];

        $this->text = "Ваш рейтинг уменьшился до $company->rating. Постарайтесь его увеличить!";

    }
}
