<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class FillCompanyRegNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:fill_reg_numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companies = Company::query()->whereNull("reg_number")
            ->get();

        $companies->each(function (Company $company){
            $company->generateFreeRegNumber();
            $company->save();
        });
        return 0;
    }
}
