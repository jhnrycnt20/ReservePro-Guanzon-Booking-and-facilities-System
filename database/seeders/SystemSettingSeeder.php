<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'resort_name' => 'Guanzon Beach',
            'resort_subtitle' => 'Bluepool Waterpark',
            'resort_email' => 'info@guanzonresort.com',
            'resort_phone' => '09190644054',
            'resort_phone_landline' => '265-7942',
            'currency' => 'PHP',
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'day_use_start' => '08:00',
            'day_use_end' => '17:00',
            'night_use_start' => '14:00',
            'night_use_end' => '00:00',
        ] as $key => $value) {
            SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
