<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AccommodationSeeder::class,
            FeedbackSeeder::class,
            ContactMessageSeeder::class,
            IncidentReportSeeder::class,
            SystemSettingSeeder::class,
        ]);
    }
}
