<?php

namespace App\Mail;

use App\Models\NotificationTypeUser;
use App\Models\Report;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefereeMadeDecision extends BaseMailer
{
    use Queueable, SerializesModels;

    public Report $report;

    public string $status;

    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report, User $recipient)
    {
        parent::__construct($this->key, $recipient->email);
        $this->report = $report;
        $this->status = match ($report->status) {
            Report::STATUSES["CONFIRMED"] => "Подтверждено",
            Report::STATUSES["REJECTED"] => "Отклонено",
            default => "",
        };
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.referee_made_decision')->subject('Арбитр принял решение');
    }
}
