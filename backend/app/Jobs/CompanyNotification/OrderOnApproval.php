<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Models\NotificationTypeUser;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderOnApproval extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Заявка требует вашего согласования';
    public string $text = 'Ваша заявка требует внимания';
    public string $key = 'order_on_approval';
    public string $resource_key = 'order_id';
    public int $resource_id;
    public array $recipients=[];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        parent::__construct();

        if (!NotificationTypeUser::isEnabled($order->user_id, 'orders', 'push')) return;

        $this->resource_id = $order->id;

        $this->recipients = [
            [
                'user_id' => $order->user_id,
                'company_id' => $order->company_id,
            ],
        ];

        $this->text = "Ваша заявка <b>$order->title</b> проверена менеджером и требует вашего внимания";
    }
}
