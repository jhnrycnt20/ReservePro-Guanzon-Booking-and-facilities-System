<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function seedGuest(array $overrides = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => 'guest'],
            ['name' => 'Guest']
        );

        return User::query()->create(array_merge([
            'role_id' => $role->id,
            'name' => 'Guest User',
            'email' => 'guest.reset@gmail.com',
            'phone' => '09171234567',
            'password' => Hash::make('Password1'),
            'is_active' => true,
        ], $overrides));
    }

    public function test_forgot_password_sends_custom_reset_notification(): void
    {
        Notification::fake();

        $user = $this->seedGuest();

        $response = $this->from('/?forgot=1')->post(route('password.email'), [
            'email' => $user->email,
            '_auth_form' => 'forgot',
        ]);

        $response->assertRedirect('/?forgot=1');
        $response->assertSessionHas('status');
        $response->assertSessionHas('password_reset_sent');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_inactive_user_cannot_request_password_reset(): void
    {
        Notification::fake();

        $user = $this->seedGuest([
            'email' => 'inactive.reset@gmail.com',
            'is_active' => false,
        ]);

        $response = $this->from('/?forgot=1')->post(route('password.email'), [
            'email' => $user->email,
            '_auth_form' => 'forgot',
        ]);

        $response->assertRedirect('/?forgot=1');
        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = $this->seedGuest(['email' => 'reset.ok@gmail.com']);
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertRedirect('/?login=1');
        $response->assertSessionHas(
            'success',
            'You have successfully reset your password. Please log in with your new password.'
        );
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_password_reset_rejects_reusing_current_password(): void
    {
        $user = $this->seedGuest([
            'email' => 'reset.same@gmail.com',
            'password' => Hash::make('Password1'),
        ]);
        $token = Password::broker()->createToken($user);

        $response = $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('Password1', $user->fresh()->password));
        $this->assertGuest();
    }
}
