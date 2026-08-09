<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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

        $users = [
            ['name' => 'System Admin', 'email' => 'admin@reservepro.test', 'phone' => '09000000001', 'role' => UserRole::Admin->value],
            ['name' => 'Front Desk Staff', 'email' => 'frontdesk@reservepro.test', 'phone' => '09000000002', 'role' => UserRole::FrontDesk->value],
            ['name' => 'Security Guard', 'email' => 'security@reservepro.test', 'phone' => '09000000003', 'role' => UserRole::Security->value],
            ['name' => 'Demo Guest', 'email' => 'guest@reservepro.test', 'phone' => '09000000004', 'role' => UserRole::Guest->value],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roles[$data['role']],
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            if ($data['role'] === UserRole::Guest->value) {
                Guest::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['contact_number' => $data['phone'], 'address' => 'Demo Address, Guanzon']
                );
            }
        }
    }
}
