<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'resort_name' => 'ReservePro Guanzon Resort',
            'resort_email' => 'hello@reservepro.test',
            'resort_phone' => '+63 900 000 0000',
            'currency' => 'PHP',
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
        ] as $key => $value) {
            SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
