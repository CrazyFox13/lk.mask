<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AdvPlace;
use App\Models\City;
use App\Models\CompanyType;
use App\Models\FormQuestion;
use App\Models\GeoRegion;
use App\Models\NotificationType;
use App\Models\Region;
use App\Models\ReportType;
use App\Models\User;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::query()->admin()->first();
        if (!$admin) {
            $this->call(AdminSeeder::class);
        }

        if (CompanyType::query()->count() === 0) {
            $this->call(CompanyTypeSeeder::class);
        }

        if (VehicleGroup::query()->count() === 0) {
            $this->call(VehicleSeeder::class);
        }

        if (ReportType::query()->count() === 0) {
            $this->call(ReportTypesSeeder::class);
        }

        // todo: will be deprecated one day
        // uses for order addresses now
        if (Region::query()->count() === 0) {
            $this->call(CitiesSeeder::class);
        }

        // users for profiles now
        if (GeoRegion::query()->count() === 0) {
            $this->call(GeoSeeder::class);
        }

        if (config("database.fake_data") === true) {
            $this->call(FakeDataSeeder::class);
        }

        if (NotificationType::query()->count() === 0) {
            $this->call(NotificationTypeSeeder::class);
        }

        if (AdvPlace::query()->count() === 0) {
            $this->call(AdvPlacesSeeder::class);
        }
    }
}
