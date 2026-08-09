<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $frontDesk = Role::query()->where('slug', 'front_desk')->firstOrFail();
        $security = Role::query()->where('slug', 'security')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'frontdesk@reservepro.test'],
            [
                'role_id' => $frontDesk->id,
                'name' => 'Front Desk Staff',
                'phone' => '09000000002',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'security@reservepro.test'],
            [
                'role_id' => $security->id,
                'name' => 'Security Guard',
                'phone' => '09000000003',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }
}
