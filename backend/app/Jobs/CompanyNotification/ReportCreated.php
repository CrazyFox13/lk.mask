<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\NotificationTypeUser;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportCreated extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'У вас новая претензия';
    public string $text = 'Вам поступила новая претензия, ознакомьтесь с ней в личном кабинете';
    public string $key = 'report_created';
    public string $resource_key = 'report_id';
    public int $resource_id;
    public array $recipients = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Report $report)
    {
        parent::__construct();

        $this->resource_id = $report->id;

        if (!NotificationTypeUser::isEnabled($report->target_user_id, 'personal', 'push')) return;

        $this->recipients = [[
            'user_id' => $report->target_user_id,
            'company_id' => $report->company_id
        ]];

        $author = $report->author;
        $senderName = "$author->name $author->surname";
        if ($company = $author->company) {
            $senderName = $company->title;
        }
        $this->text = "Вам поступила новая претензия <b>№$report->id от $senderName</b>";

    }
}
