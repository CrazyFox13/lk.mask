<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmation extends BaseMailer
{
    use Queueable, SerializesModels;

    public User $user;
    public string $url;
    public string $key = "personal";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        parent::__construct($this->key, $user->email);
        $this->user = $user;
        $this->url = url("/api/auth/verify-email?user_id=$user->id&hash=" . $user->getEmailConfirmationHash());
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $appName = config('app.name');
        return $this->view('emails.email_confirmation')->subject("Подтверждение e-mail на $appName");
    }
}
