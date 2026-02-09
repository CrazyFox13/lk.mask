<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ReservedNumber;
use Illuminate\Console\Command;

class ImportVIPNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reserved_numbers:import';

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
        $filePath = storage_path('app/vip_numbers.txt');
        $rawData = file_get_contents($filePath);
        $numbers = array_map('trim', explode("\n", $rawData));
        $models = array_map(function ($n) {
            return ['number' => $n];
        }, $numbers);
        ReservedNumber::query()->upsert($models, 'number');
        $companies = Company::query()
            ->whereIn("reg_number", $numbers)
            ->get();

        $companies->each(function (Company $company) {
            $company->generateFreeRegNumber();
            $company->save();
        });
        return 0;
    }
}
