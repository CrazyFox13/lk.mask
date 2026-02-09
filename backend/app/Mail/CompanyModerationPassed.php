<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\NotificationTypeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyModerationPassed extends BaseMailer
{
    use Queueable, SerializesModels;

    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        parent::__construct($this->key, $company->boss->email);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.company_moderation_passed')->subject('Компания подтверждена');
    }
}
