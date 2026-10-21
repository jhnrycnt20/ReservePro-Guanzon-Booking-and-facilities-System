<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffThemeTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Theme Tester',
            'email' => $slug.'@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    public function test_staff_panels_include_the_theme_switch_and_stylesheet(): void
    {
        foreach (['admin', 'front_desk', 'security'] as $role) {
            $response = $this->actingAs($this->userWithRole($role))
                ->get(route($role.'.dashboard'));

            $response->assertOk()
                ->assertSee('data-rp-theme-toggle', false)
                ->assertSee('staff-theme.css', false)
                ->assertSee('rp-staff-ui', false);
        }
    }

    public function test_guest_pages_do_not_include_the_staff_theme(): void
    {
        $response = $this->actingAs($this->userWithRole('guest'))
            ->get(route('guest.bookings.index'));

        $response->assertOk()
            ->assertDontSee('data-rp-theme-toggle', false)
            ->assertDontSee('staff-theme.css', false);
    }
}
