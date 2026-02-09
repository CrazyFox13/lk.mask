<?php

namespace Database\Seeders;

use App\Models\FormQuestion;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    const TYPES = [
        [
            'title' => 'Землеройная техника',
            'types' => [],
        ],
        [
            'title' => 'Грузовой транспорт',
            'types' => [
                [
                    'title' => "Автобетоносмеситель",
                    'form_questions' => [
                        [
                            "type" => "text",
                            "label" => "Объем кузова",
                            "order" => 0,
                            "required" => true,
                        ],
                        [
                            "type" => "select",
                            "label" => "Производитель",
                            "order" => 1,
                            "required" => true,
                            "options" => [
                                "Отечественный", "Зарубежный"
                            ]
                        ],
                        [
                            "type" => "checkbox",
                            "label" => "С водителем",
                            "order" => 2,
                            "required" => false,
                        ],
                    ]
                ],
                [
                    'title' => "Бензовоз",
                    'form_questions' => [
                        [
                            "type" => "text",
                            "label" => "Объем кузова",
                            "order" => 0,
                            "required" => true,
                        ],
                        [
                            "type" => "select",
                            "label" => "Кол-во осей",
                            "order" => 1,
                            "required" => true,
                            "options" => [
                                "до 4", "больше 4"
                            ]
                        ],
                        [
                            "type" => "checkbox",
                            "label" => "С водителем",
                            "order" => 2,
                            "required" => false,
                        ],
                    ]
                ],
                [
                    'title' => "Грузовик",
                    'form_questions' => [
                        [
                            "type" => "text",
                            "label" => "Объем кузова",
                            "order" => 0,
                            "required" => true,
                        ],
                        [
                            "type" => "select",
                            "label" => "Тип",
                            "order" => 1,
                            "required" => true,
                            "options" => [
                                "Открытый", "Закрытый", "Фура", "Холодильник"
                            ]
                        ],
                        [
                            "type" => "checkbox",
                            "label" => "С водителем",
                            "order" => 2,
                            "required" => false,
                        ],
                    ]
                ],
                [
                    'title' => "Пухтовоз",
                    'form_questions' => []
                ],
                [
                    'title' => "Самосвал",
                    'form_questions' => [
                        [
                            "type" => "text",
                            "label" => "Объем кузова",
                            "order" => 0,
                            "required" => true,
                        ],
                        [
                            "type" => "select",
                            "label" => "Высота",
                            "order" => 1,
                            "required" => true,
                            "options" => [
                                "До 4х метров", "Выше 4х метров",
                            ]
                        ],
                        [
                            "type" => "checkbox",
                            "label" => "С водителем",
                            "order" => 2,
                            "required" => false,
                        ],
                    ]
                ],
                [
                    'title' => "Седельный тягач",
                    'form_questions' => []
                ],
                [
                    'title' => "Эвакуатор",
                    'form_questions' => [
                        [
                            "type" => "integer",
                            "label" => "Кол-во машиномест",
                            "order" => 0,
                            "required" => true,
                        ],
                        [
                            "type" => "select",
                            "label" => "Длина",
                            "order" => 1,
                            "required" => true,
                            "options" => [
                                "До 4х метров", "Выше 4х метров",
                            ]
                        ],
                        [
                            "type" => "checkbox",
                            "label" => "С водителем",
                            "order" => 2,
                            "required" => false,
                        ],
                    ]
                ],
            ],
        ],
        [
            'title' => 'Дорожная техника',
            'types' => [],
        ],
        [
            'title' => 'Строительная техника',
            'types' => [],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Creating default vehicles...\n";
        foreach (self::TYPES as $vehicleGroup) {
            $group = new VehicleGroup();
            $group->title = $vehicleGroup['title'];
            $group->save();
            foreach ($vehicleGroup['types'] as $vehicleType) {
                $type = new VehicleType();
                $type->vehicle_group_id = $group->id;
                $type->title = $vehicleType['title'];
                $type->save();

                foreach ($vehicleType['form_questions'] as $formQuestion) {
                    $question = new FormQuestion($formQuestion);
                    $question->vehicle_type_id = $type->id;
                    $question->save();
                }
            }
        }
        echo "Default vehicles created\n";
    }
}
