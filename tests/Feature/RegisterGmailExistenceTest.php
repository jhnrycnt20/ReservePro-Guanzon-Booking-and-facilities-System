<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\GmailExistenceChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegisterGmailExistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
    }

    public function test_nonexistent_gmail_is_blocked_on_register(): void
    {
        $this->mock(GmailExistenceChecker::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->once()
                ->with('arowaxxx@gmail.com')
                ->andReturn(false);
        });

        $response = $this->from('/?signup=1')->post('/register', [
            'name' => 'Fake Guest',
            'email' => 'arowaxxx@gmail.com',
            'phone' => '09171234567',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertRedirect('/?signup=1');
        $response->assertSessionHasErrors(['email'], null, 'register');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_https_probe_marks_unregistered_gmail_as_missing(): void
    {
        Http::fake([
            'gmail.k1god.com/*' => Http::response([
                'success' => true,
                'results' => [[
                    'email' => 'arowaxxx@gmail.com',
                    'status' => 'Unregistered',
                    'category' => 'Unregistered',
                ]],
            ], 200),
        ]);

        // Force HTTPS path (SMTP may still run first in local; stub by using production order).
        app()->detectEnvironment(fn () => 'production');

        $checker = app(GmailExistenceChecker::class);
        $this->assertFalse($checker->exists('arowaxxx@gmail.com'));
    }
}
