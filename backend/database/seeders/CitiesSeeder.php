<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CitiesSeeder extends Seeder
{
    const DISK = 'local';

    const FILE_URL = 'koord_russia.csv';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Uploading cities\n";
        Region::query()->delete();
        City::query()->delete();
        $data = Storage::disk(self::DISK)->get(self::FILE_URL);
        $data = iconv('CP1251', 'UTF-8', $data);
        $federalCities = ["Москва", "Санкт-Петербург", "Севастополь"];
        foreach (explode("\n", $data) as $k => $row) {
            if (!$k) continue;
            if (!trim($row)) continue;
            list($cityName, $regionName, $regionType) = explode(";", trim($row));
            $region = Region::query()->where('title', $regionName)->first();
            if (!$region) {
                $region = new Region(['title' => $regionName, 'type' => $regionType]);
                $region->save();
            }
            if (in_array($cityName, $federalCities)) {
                continue;
            }
            $city = new City(['title' => $cityName, 'region_id' => $region->id]);
            $city->save();
        }

        echo "Cities uploaded\n";
    }
}
