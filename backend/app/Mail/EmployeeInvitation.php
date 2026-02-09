<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitation extends BaseMailer
{
    use Queueable, SerializesModels;

    public User $user;
    public Company $company;
    public User $boss;
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
        $this->company = $user->company;
        $this->boss = $this->company->boss;
        $this->url = url("/api/auth/verify-email?user_id=$user->id&hash=" . $user->getEmailConfirmationHash());
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $app = config('app.name');
        return $this->view('emails.employee_invitation')->subject("Приглашение в приложение $app");
    }
}
