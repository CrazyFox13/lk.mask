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

class CompanyModerationPassed extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Ваша компания подтверждена';
    public string $text = 'Ваша организация проверена и подтверждена. Теперь вы можете полноценно пользоваться сервисом';
    public string $key = 'company_moderation_passed';
    public string $resource_key = 'company_id';
    public int $resource_id;
    public array $recipients = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        parent::__construct();

        $this->resource_id = $company->id;

        if (!NotificationTypeUser::isEnabled($company->boss->id, 'personal', 'push')) return;

        $this->recipients = [[
            'user_id' => $company->boss->id,
            'company_id' => $company->id
        ]];
    }
}
