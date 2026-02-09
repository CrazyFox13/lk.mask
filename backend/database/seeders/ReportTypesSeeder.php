<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportTypesSeeder extends Seeder
{
    const TYPES = ['Причинение ущерба', 'Некачественное оборудование', 'Неорганизованные работники'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Creating default report types...\n";

        foreach (self::TYPES as $reportType) {
            $type = new ReportType([
                'title' => $reportType
            ]);
            $type->save();
        }
        echo "Default report types created\n";
    }
}
