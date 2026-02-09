<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderOfferCreated extends BaseMailer
{
    use Queueable, SerializesModels;

    public string $key = 'orders';

    public Order $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OrderOffer $orderOffer)
    {

        /** @var Order $order */
        $order = $orderOffer->order()->first();
        $this->order = $order;

        $user = $order->user()->first();

        parent::__construct($this->key, $user->email);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.order_offer_created')->subject("Получено предложение по заявке");
    }
}
