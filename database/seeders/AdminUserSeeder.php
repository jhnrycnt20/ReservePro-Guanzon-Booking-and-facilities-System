<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('slug', 'admin')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@reservepro.test'],
            [
                'role_id' => $role->id,
                'name' => 'System Administrator',
                'phone' => '09000000001',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }
}
