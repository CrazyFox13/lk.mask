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

class RefereeMadeDecision extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Арбитр принял решение';
    public string $text = 'Арбитр принял решение, претензия №_номер претензии_ _статус_.';
    public string $key = 'report_completed';
    public string $resource_key = 'report_id';
    public int $resource_id;
    public array $recipients=[];

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

        $this->recipients = [
            [
                'user_id' => $report->author_user_id,
                'company_id' => null,
            ],
            [
                'user_id' => $report->target_user_id,
                'company_id' => $report->company_id,
            ],
        ];

        $status = match ($report->status) {
            Report::STATUSES["CONFIRMED"] => "Подтверждено",
            Report::STATUSES["REJECTED"] => "Отклонено",
            default => "",
        };
        $this->text = "Арбитр принял решение, претензия <b>№$report->id. $status</b>";
    }
}
