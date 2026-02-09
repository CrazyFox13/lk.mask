<?php

namespace Database\Seeders;

use App\Models\CsvImporter;
use App\Models\GeoCity;
use App\Models\GeoRegion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $regions = $this->parse(storage_path('app/region.csv'));
        $cities = $this->parse(storage_path('app/city.csv'));
        foreach ($regions as $region) {
            $regionModel = new GeoRegion();
            $regionModel->fill([
                'name' => $region['name'],
                'name_with_type' => $region['name_with_type'],
                'type' => $region['type'],
                'federal_district' => $region['federal_district'],
                'postal_code' => $region['postal_code'],
                'timezone' => $region['timezone'],
                'geoname_id' => $region['geoname_id'],
                'fias_id' => $region['fias_id']
            ]);
            $regionModel->save();

            $regionCities = array_filter($cities, function ($item) use ($regionModel) {
                return $item['region'] === $regionModel->name && $item['federal_district'] === $regionModel->federal_district;
            });

            $toInsert = array_map(function ($city) use ($regionModel) {
                return [
                    'geo_region_id' => $regionModel->id,
                    'name' => $city['city'],
                    'postal_code' => $city['postal_code'],
                    'timezone' => $city['timezone'],
                    'lat' => $city['geo_lat'],
                    'lng' => $city['geo_lon'],
                    'fias_id' => $city['fias_id'],
                ];
            }, $regionCities);

            GeoCity::insert($toInsert);
        }
    }

    protected function parse($file): array
    {
        $importer = new CsvImporter($file, true, ',');
        return $importer->get();
    }
}
