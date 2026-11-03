<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_email_shows_validation_instead_of_server_error(): void
    {
        $role = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        User::query()->create([
            'role_id' => $role->id,
            'name' => 'Existing Guest',
            'email' => 'taken.guest@gmail.com',
            'phone' => '09171234567',
            'password' => 'Password1',
            'is_active' => true,
        ]);

        $response = $this->from('/?signup=1')->post('/register', [
            'name' => 'Another Guest',
            'email' => 'taken.guest@gmail.com',
            'phone' => '09181234567',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertRedirect('/?signup=1');
        $response->assertSessionHasErrors(['email'], null, 'register');
        $this->assertDatabaseCount('users', 1);
    }
}
