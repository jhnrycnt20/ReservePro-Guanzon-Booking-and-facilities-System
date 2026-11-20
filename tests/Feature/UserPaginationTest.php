<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_ten_users_per_page(): void
    {
        $adminRole = Role::query()->create(['name' => 'Administrator', 'slug' => 'admin']);
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $admin = User::query()->create([
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);

        foreach (range(1, 10) as $number) {
            User::query()->create([
                'role_id' => $guestRole->id,
                'name' => "Guest {$number}",
                'email' => "guest{$number}@example.com",
                'password' => 'StrongPass123',
                'is_active' => true,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->perPage() === 10
                && $users->count() === 10
                && $users->total() === 11);
    }
}
