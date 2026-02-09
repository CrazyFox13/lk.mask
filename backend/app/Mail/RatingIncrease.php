<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\NotificationTypeUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RatingIncrease extends BaseMailer
{
    use Queueable, SerializesModels;

    public Company $company;

    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        parent::__construct($this->key, $company->boss->email);
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.rating_increase')->subject('Рейтинг вырос');
    }
}
