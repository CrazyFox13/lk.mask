<?php

namespace App\Jobs;

use App\Mail\NewOrder;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderFilter;
use App\Models\SubscribedGeo;
use App\Models\SubscribedVehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewOrderEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public array $recipients;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        $subscribedVehicles = SubscribedVehicle::query()
            ->whereHas('user', function ($q) use ($order) {
                $q->notificationEnabled('vehicle_orders', 'email')
                    ->notInCompany($order->company_id)
                    ->where(function ($q) use ($order) {
                        // where doesnt have SubscribedGeo
                        $q->doesntHave("subscribedCities")
                            // or whereHas SubscribedGeo whereIn geo_city_id $order->addresses()->pluck("geo_city_id")
                            ->orWhereHas("subscribedCities", function ($q) use ($order) {
                                $q->whereIn("geo_city_id", $order->addresses()->pluck("geo_city_id"));
                            });
                    });
            })
            ->with("user")
            ->where("vehicle_type_id", $order->vehicle_type_id)
            ->get();

        $orderFilters = OrderFilter::query()->whereHas('user', function ($q) use ($order) {
            $q->notificationEnabled('filters', 'email')
                ->notInCompany($order->company_id);
        })->with('user')->activeEmail()->passes($order)->get();

        $recipients = $orderFilters->merge($subscribedVehicles)/*->merge($subscribedGeo)*/ ->map(function ($collection) {
            return [
                'email' => $collection->user->getRawOriginal('email'),
                'delay' => $collection->user->getSilenceDelay(),
            ];
        });

        $this->recipients = $recipients->filter()->unique('email')->toArray();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->recipients as $recipient) {
            if (!$recipient['email']) continue;
            if ($recipient['email'] == "* * * * * *") {
                continue;
            }
            Mail::to($recipient['email'])->later(now()->addSeconds($recipient['delay']), new NewOrder($this->order, $recipient['email']));
        }
    }
}
