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

class OrderDateExpired extends GenerateCompanyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $subject = 'Заявка снята с публикации';
    public string $text = 'У вашей заявки _заголовок заявки_ просрочена дата начала работ.';
    public string $key = 'order_expired';
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

        $this->resource_id = $order->id;

        if (!NotificationTypeUser::isEnabled($order->user_id, 'orders', 'push')) return;

        $this->recipients = [
            [
                'user_id' => $order->user_id,
                'company_id' => $order->company_id,
            ],
        ];

        $this->text = "У вашей заявки <b>$order->title</b> просрочена дата начала работ.";

    }
}
