<?php

namespace App\Console\Commands;

use App\Jobs\CompanyNotification\OrderDateExpired;
use App\Models\Order;
use Illuminate\Console\Command;

class PassedOrdersObserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete_passed_orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark as complete orders with passed start date on 5 and more days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::query()
            ->approved()
            ->expired()
            ->get();

        Order::query()
            ->whereIn("id", $orders->pluck("id"))
            ->update([
                'moderation_status' => Order::MODERATION_STATUSES['REMOVED']
            ]);

        $orders->each(function (Order $order) {
            dispatch(new OrderDateExpired($order));
        });

        return 0;
    }
}
