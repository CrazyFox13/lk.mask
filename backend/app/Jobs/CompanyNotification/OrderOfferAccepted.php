<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\NotificationTypeUser;
use App\Models\OrderOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderOfferAccepted extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Предложение по заявке одобрено';
    public string $text = 'Ваше предложение по заявке (заголовок заявки) было одобрено';
    public string $key = 'order_offer_accepted';
    public string $resource_key = 'order_id';
    public int $resource_id;
    public array $recipients = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderOffer $orderOffer)
    {
        parent::__construct();

        $order = $orderOffer->order()->first();

        $this->resource_id = $order->id;

        $this->recipients = [
            [
                'user_id' => $orderOffer->user_id,
                'company_id' => $orderOffer->company_id,
            ],
        ];

        $this->additional_data=[
            'company_id' => $orderOffer->company_id,
        ];

        $this->text = "Ваше предложение по заявке \"{$order->title}\" было одобрено";
    }
}
