<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestDashboardRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_dashboard_shortcut_redirects_to_bookings(): void
    {
        $role = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/guest')
            ->assertRedirect(route('guest.bookings.index'));
    }
}
