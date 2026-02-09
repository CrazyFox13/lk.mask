<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTempPassword extends BaseMailer
{
    use Queueable, SerializesModels;

    public User $user;

    public string $password;

    public string $key = '';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $password)
    {
        parent::__construct($this->key, $user->getRawOriginal("email"));
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.temp_password')->subject("Временный пароль для доступа на сайт");
    }
}
