<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrder extends BaseMailer
{
    use Queueable, SerializesModels;

    public Order $order;
    public User|null $user;
    public string $vehicleTitle;
    public string $key = "filters";

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, string $email)
    {
        parent::__construct($this->key, $email);
        $this->order = $order;
        $this->user = User::where("email", $email)->first();
        $this->vehicleTitle = $order->vehicleType?->title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.new_order')->subject("Новая заявка");
    }
}
