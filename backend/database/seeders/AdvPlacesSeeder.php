<?php

namespace Database\Seeders;

use App\Models\AdvPlace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdvPlacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $places = [
            [
                "key" => "main_1",
                "name" => "Главная (1)",
                "width" => 285,
                "height" => 428,
                "with_vehicle_filter" => false
            ],
            [
                "key" => "main_2",
                "name" => "Главная (2)",
                "width" => 285,
                "height" => 428,
                "with_vehicle_filter" => false
            ],
            [
                "key" => "orders_list_1",
                "name" => "Список заявок (1)",
                "width" => 285,
                "height" => 428,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "orders_list_2",
                "name" => "Список заявок (2)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "orders_list_3",
                "name" => "Список заявок (3)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "orders_item",
                "name" => "Детальная страница заявки",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "companies_list_1",
                "name" => "Список исполнителей (1)",
                "width" => 285,
                "height" => 428,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "companies_list_2",
                "name" => "Список исполнителей (2)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "companies_list_3",
                "name" => "Список исполнителей (3)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "companies_item",
                "name" => "Детальная страница исполнителя",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => true
            ],
            [
                "key" => "favorite_1",
                "name" => "Избранное (1)",
                "width" => 285,
                "height" => 428,
                "with_vehicle_filter" => false
            ],
            [
                "key" => "favorite_2",
                "name" => "Избранное (2)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => false
            ],
            [
                "key" => "favorite_3",
                "name" => "Избранное (3)",
                "width" => 285,
                "height" => 214,
                "with_vehicle_filter" => false
            ],
        ];
        foreach ($places as $place) {
            $model = new AdvPlace($place);
            $model->save();
        }
    }
}
