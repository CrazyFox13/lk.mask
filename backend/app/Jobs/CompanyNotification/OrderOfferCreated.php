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

class OrderOfferCreated extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Получено предложение по заявке';
    public string $text = 'Вы получили новое предложение по заявке (заголовок заявки).';
    public string $key = 'order_offer_created';
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
                'user_id' => $order->user_id,
                'company_id' => $order->company_id,
            ],
        ];

        $this->additional_data=[
            'company_id' => $order->company_id,
        ];

        $this->text = "Вы получили новое предложение по заявке \"{$order->title}\".";
    }
}
