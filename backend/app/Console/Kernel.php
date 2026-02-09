<?php

namespace App\Console;

use App\Console\Commands\CollectOnlineHistory;
use App\Console\Commands\DispatchCustomPushNotifications;
use App\Console\Commands\ModerateNewOrders;
use App\Console\Commands\PassedOrdersObserver;
use App\Console\Commands\RecalculateCompaniesRating;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command(ModerateNewOrders::class)->everyMinute();
        $schedule->command(DispatchCustomPushNotifications::class)->everyMinute();
        $schedule->command(RecalculateCompaniesRating::class)->twiceDaily();
        $schedule->command(PassedOrdersObserver::class)->twiceDaily();
        $schedule->command(CollectOnlineHistory::class)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
