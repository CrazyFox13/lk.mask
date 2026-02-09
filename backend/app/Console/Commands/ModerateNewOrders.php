<?php

namespace App\Console\Commands;

use App\Jobs\AutoModerationOrder;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ModerateNewOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:moderate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto moderation of orders after N min delay';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = Order::query()
            ->moderation()
            ->whereNotNull('sent_on_moderation_at')
            ->where("sent_on_moderation_at", "<", Carbon::now()->subMinutes(Order::AUTO_MODERATION_DELAY))
            ->get();

        $orders->each(function (Order $order) {
            $order->passModeration();
        });
        //AutoModerationOrder::dispatch($this->id)->delay(now()->addMinutes(5));
        return 0;
    }
}
