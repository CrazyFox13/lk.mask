<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends BaseMailer
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetLink;
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
        $this->resetLink = $user->getEmailResetLink();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $appName = config('app.name');
        return $this->view('emails.reset_password')->subject("Сброс пароля в $appName");
    }
}
