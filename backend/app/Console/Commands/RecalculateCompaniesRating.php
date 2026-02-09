<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class RecalculateCompaniesRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rating:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates rating of all companies on platform';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Company::query()
            ->published()
            ->get()->each(function (Company $company) {
                $company->updateRating();
            });
        return 0;
    }
}
