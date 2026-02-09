<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectOnlineHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'online:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save DAU';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $toPeriod = Carbon::now();
        $fromPeriod = Carbon::now()->subDay();
        $count = User::query()
            ->user()
            ->where("last_online_datetime", ">=", $fromPeriod)
            ->where("last_online_datetime", "<=", $toPeriod)
            ->count();

        DB::table("online_history")
            ->insert([
                'created_at' => $toPeriod,
                'users_count' => $count,
            ]);
        return 0;
    }
}
