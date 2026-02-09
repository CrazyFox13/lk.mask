<?php

use App\Jobs\SendTelegramPost;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\NotificationType;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('test:phone {phone}', function ($phone) {
    $v = in_array(str_replace(["+", " "], "", $phone), User::RESERVED_PHONES);
    var_dump($v);
})->purpose('Display an inspiring quote');

Artisan::command("rating:test {id}", function ($id) {
    $company = \App\Models\Company::query()->find($id);
    if (!$company) exit(1);
    $calc = new \App\Services\CompanyRatingCalculator($company);
    dd($calc->getScore());
});

Artisan::command("attach_notifications", function () {
    User::query()->doesntHave("notificationTypes")->get()
        ->each(function (User $user) {
            NotificationType::attachNotifications($user);
        });
});

Artisan::command("test-order-filter", function () {
    $order = \App\Models\Order::query()->find(575);

    $filters = \App\Models\OrderFilter::query()
        ->active()
        ->passes($order)
        ->get();
    dd($filters->toArray());
});
Artisan::command("del-file", function () {
    $url = "https://astt.storage.yandexcloud.net/139/1682080082.jpg";
    list('path' => $path) = parse_url($url);
    $c = \Illuminate\Support\Facades\Storage::disk('s3')->get($path);
});

Artisan::command("carb", function () {
    $timestamp = 818294400000 / 1000;
    $format = "d.m.Y";

    $date1 = \Carbon\Carbon::parse($timestamp)->format($format);
    $date2 = date($format, $timestamp);

    $date3 = new \DateTime();
    $date3->setTimestamp($timestamp);
    $date3 = $date3->format($format);
    dd($date1, $date2, $date3);
});


Artisan::command("fake_history", function () {
    $to = \Carbon\Carbon::now();
    $from = \Carbon\Carbon::now()->subMonth();
    $pointer = \Carbon\Carbon::parse($from);
    while ($pointer <= $to) {
        \Illuminate\Support\Facades\DB::table('online_history')->insert([
            'users_count' => random_int(10, 100),
            'created_at' => $pointer,
        ]);
        $pointer->addDay();
    }
});


Artisan::command("users:password-reset", function () {
    User::query()->update([
        'password' => \Illuminate\Support\Facades\Hash::make("123456")
    ]);
});


Artisan::command("send-tg {id}", function ($id) {
    $order = \App\Models\Order::query()->find($id);
    dispatch(new SendTelegramPost($order));
});


Artisan::command("ws:channels", function () {
    $connection = config('broadcasting.connections.pusher');

    $pusher = new Pusher\Pusher(
        $connection['key'],
        $connection['secret'],
        $connection['app_id'],
        [
            'cluster' => "ru1",
            'useTLS' => true,
            'host' => $connection['options']["host"],
            'port' => $connection['options']["port"],
            'scheme' => $connection['options']["scheme"],
        ]
    );

    $channel = $pusher->get("/channels/private-chat-218");
    dd($channel->occupied);
});



