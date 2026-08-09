<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('slug', 'guest')->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => 'guest@reservepro.test'],
            [
                'role_id' => $role->id,
                'name' => 'Sample Guest',
                'phone' => '09000000004',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        Guest::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'contact_number' => '09000000004',
                'address' => 'Sample Address, Philippines',
                'emergency_contact' => '09000000005',
                'id_type' => 'Passport',
                'id_number' => 'P1234567',
            ]
        );
    }
}
