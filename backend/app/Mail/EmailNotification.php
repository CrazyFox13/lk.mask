<?php

namespace App\Mail;

use App\Models\PushNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EmailNotification extends BaseMailer
{
    use Queueable, SerializesModels;

    protected User $user;
    public PushNotification $pushNotification;
    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PushNotification $notification, User $user)
    {
        parent::__construct($this->key, $user->email);
        $this->user = $user;
        $this->pushNotification = $notification;
    }

    /**
     * @return EmailNotification
     */
    public function build()
    {
        return $this->view('emails.email_notification')->subject($this->pushNotification->subject);
    }
}
