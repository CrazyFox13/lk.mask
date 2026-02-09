<?php

namespace App\Jobs;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         *
         *curl -X 'POST' \
         * 'http://localhost:33033/send-message' \
         * -H 'accept: application/json' \
         * -H 'Content-Type: application/json' \
         * -d '{
         * "type_name": "default",
         * "variable": {"name": " Новая заявка на Автокран г/п 32 тонн стрела 32м. вездеход.",
         * "place": "Сергиев Посад","price": "26 000 ₽ нал., 27 000 ₽ без НДС, 28 000 ₽ НДС", "url": "https://astt.su/orders/4253 "}
         * }'
         *
         */
        $client = new Client([
            'base_uri' => config("services.tg_bot.url"),
            'headers' => [
                'accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $orderId = $this->order->id;
        $place = $this->order->startAddress->city->name ?? null;
        if (!$this->order->amount_by_agreement) {
            $prices = [];
            if ($this->order->amount_cash) $prices[] = number_format($this->order->amount_cash, 0, '.', ' ') . " ₽ нал.";
            if ($this->order->amount_account) $prices[] = number_format($this->order->amount_account, 0, '.', ' ') . " ₽ без НДС";
            if ($this->order->amount_account_vat) $prices[] = number_format($this->order->amount_account_vat, 0, '.', ' ') . " ₽ НДС";

            $formattedString = implode(",", $prices);
        }


        $response = $client->post('/send-message', [
            'json' => [
                'type_name' => 'default',
                'variable' => [
                    'name' => "Новая заявка на " . $this->order->title,
                    'place' => $place,
                    'price' => $formattedString ?? "По договоренности",
                    'url' => "https://astt.su/orders/$orderId"
                ]
            ]
        ]);

        $body = $response->getBody();
        Log::info("SENT TO TG", ["body" => $body]);
    }
}
