<?php

namespace Database\Seeders;

use App\Models\CompanyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyTypeSeeder extends Seeder
{
    const TYPES = [
        [
            'title' => 'Поставщик',
            'is_worker' => true,
        ],
        [
            'title' => 'Заказчик',
            'is_worker' => false,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Insert company types...\n";
        CompanyType::insert(self::TYPES);
        echo "Company types loaded\n";
    }
}
