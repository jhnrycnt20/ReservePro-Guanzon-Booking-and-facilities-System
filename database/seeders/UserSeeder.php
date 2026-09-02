<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'slug');
        $password = config('demo.password', 'password');

        foreach (config('demo.accounts', []) as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roles[$data['role']],
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]
            );

            if ($data['role'] === 'guest') {
                Guest::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'contact_number' => $data['phone'],
                        'address' => $data['guest_address'] ?? 'Demo Address, Guanzon',
                    ]
                );
            }
        }
    }
}
