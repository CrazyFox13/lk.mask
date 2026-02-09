<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $form;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $form)
    {
        $this->form = $form;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.support')->subject("Новый запрос в поддержку");
    }
}
