<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Creating admin...\n";
        $user = new User([
            'name' => 'Admin',
            'surname' => config("app.name"),
            'email' => 'admin@astt.su',
            'phone' => '70000000000',
            'password' => Hash::make(123456),
        ]);
        $user->type = User::USER_TYPES[2];
        $user->save();

        echo "Admin created...\n";
    }
}
