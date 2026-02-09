<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class BaseMailer extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $key;
    public string $unsubscribeHash;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($key, $email)
    {
        $this->email = $email;
        $this->key = $key;
        $this->unsubscribeHash = Hash::make("astt-unsubscribe-$this->email");
    }
}
