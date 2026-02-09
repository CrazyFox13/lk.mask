<?php

namespace App\Jobs\CompanyNotification;

use App\Jobs\GenerateCompanyNotification;
use App\Jobs\SendPush;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderFilter;
use App\Models\SubscribedGeo;
use App\Models\SubscribedVehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;

    public string $subject = 'Новая заявка';
    public string $text = 'Появилась новая заявка на вашу технику';
    public array $recipients = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->generateTitle();

        $subscribedVehicles = SubscribedVehicle::query()
            ->whereHas('user', function ($q) use ($order) {
                $q->notificationEnabled('vehicle_orders', 'push')
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
            ->get()->map(function (SubscribedVehicle $subscribedVehicle) {
                $user = $subscribedVehicle->user;
                return [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ];
            });

        $orderFilters = OrderFilter::query()->whereHas('user', function ($q) use ($order) {
            $q->notificationEnabled('filters', 'push')
                ->notInCompany($order->company_id);
        })->with('user')->activePush()->passes($order)->get();

        $filterRecipients = $orderFilters->map(function (OrderFilter $filter) {
            return [
                'user_id' => $filter->user_id,
                'company_id' => $filter->user->company_id
            ];
        });

        $this->recipients = $filterRecipients
            /*->merge($subscribedGeo)*/
            ->merge($subscribedVehicles)->unique('user_id')->toArray();
    }

    public function handle(): void
    {
        $order = $this->order;

        $users = User::query()->has("device")->with("device")->whereIn("id", array_column($this->recipients, "user_id"))->get();
        $users->each(function (User $usr) use ($order) {
            $delay = $usr->getSilenceDelay();
            $allBadges = $usr->countBadges();
            $text = str_replace("<br/>", "\n", $this->text);
            dispatch(new SendPush(device: $usr->device, title: $this->subject, text: strip_tags($text), action: "", data: [
                "key" => "new_order",
                "order_id" => $order->id,
            ], badgesCount: $allBadges['total_count']))->delay(now()->addSeconds($delay));
        });
    }

    protected function generateTitle()
    {
        $order = $this->order;
        $this->subject = $order->title;
        if ($order->amount_by_agreement) {
            $price = "По договорённости";
        } else {
            $prices = [];
            if ((int)$order->amount_account_vat > 0) {
                $number = number_format($order->amount_account_vat, 0, ',', ' ');
                $prices[] = "НДС $number" . "₽";
            }
            if ((int)$order->amount_account > 0) {
                $number = number_format($order->amount_account, 0, ',', ' ');
                $prices[] = "Без НДС $number" . "₽";
            }
            if ((int)$order->amount_cash > 0) {
                $number = number_format($order->amount_cash, 0, ',', ' ');
                $prices[] = "нал. $number" . "₽";
            }

            $price = implode(" · ", $prices);
        }
        $this->text = "$price<br/>новая заявка";
    }
}
