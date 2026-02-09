<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        NotificationType::query()->upsert(NotificationType::TYPES, ['key'], [
            'title', 'description'
        ]);


        $users = User::query()->pluck("id");

        NotificationType::query()->doesntHave("users")
            ->get()->each(function (NotificationType $type) use ($users) {
                var_dump("type", $type->title);
                $type->users()->attach($users, ['way' => 'push', 'active' => true]);
                $type->users()->attach($users, ['way' => 'email', 'active' => true]);
            });
    }
}
