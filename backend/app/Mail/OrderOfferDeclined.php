<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class OrderOfferDeclined extends BaseMailer
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
        $user = $orderOffer->user()->first();
        parent::__construct($this->key, $user->email);

        /** @var Order $order */
        $order = $orderOffer->order()->first();
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.order_offer_declined')->subject("Предложение по заявке отклонено");
    }
}
