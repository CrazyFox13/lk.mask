<?php

namespace App\Mail;

use App\Models\NotificationTypeUser;
use App\Models\Report;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportCreated extends BaseMailer
{
    use Queueable, SerializesModels;

    public Report $report;

    public string $author;

    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report)
    {
        $recipient = User::query()->find($report->target_user_id);

        parent::__construct($this->key, $recipient->email);

        $this->report = $report;
        $authorUser = $report->author()->first();
        $this->author = "$authorUser->name $authorUser->surname";

        $company = $authorUser->company()->published()->first();
        if ($company) {
            $this->author = $company->title;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.report_created')->subject('У вас новая претензия');
    }
}
