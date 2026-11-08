<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileValidationTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $slug): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst(str_replace('_', ' ', $slug))]);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Demo Staff',
            'email' => str_replace('_', '', $slug).'@reservepro.test',
            'phone' => '09000000001',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    private function profilePayload(User $user, array $changes = []): array
    {
        return array_merge([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            '_modal' => 'profile',
        ], $changes);
    }

    public function test_all_staff_roles_can_update_details_without_changing_a_demo_email(): void
    {
        foreach (['admin', 'front_desk', 'security'] as $slug) {
            $user = $this->staff($slug);

            $this->actingAs($user)->put(route('profile.update'), $this->profilePayload($user, [
                'name' => 'Updated Staff',
                'phone' => '+63 (917) 123-4567',
            ]))->assertSessionHasNoErrors();

            $this->assertSame('Updated Staff', $user->fresh()->name);
            $this->assertSame('09171234567', $user->fresh()->phone);
            $this->assertSame($slug, $user->fresh()->role->slug);
        }
    }

    public function test_all_staff_roles_must_confirm_email_changes_with_their_current_password(): void
    {
        foreach (['admin', 'front_desk', 'security'] as $slug) {
            $user = $this->staff($slug);
            $newEmail = 'updated'.str_replace('_', '', $slug).'@gmail.com';
            $payload = $this->profilePayload($user, ['email' => $newEmail]);

            $this->actingAs($user)->put(route('profile.update'), $payload)
                ->assertSessionHasErrors(['current_password']);
            $this->actingAs($user)->put(route('profile.update'), [
                ...$payload,
                'current_password' => 'WrongPass123',
            ])->assertSessionHasErrors(['current_password']);
            $this->assertSame($user->email, $user->fresh()->email);

            $this->actingAs($user)->put(route('profile.update'), [
                ...$payload,
                'current_password' => 'StrongPass123',
            ])->assertSessionHasNoErrors();
            $this->assertSame($newEmail, $user->fresh()->email);
        }
    }

    public function test_changed_email_still_requires_gmail(): void
    {
        $user = $this->staff('admin');

        $this->actingAs($user)->put(route('profile.update'), $this->profilePayload($user, [
            'email' => 'newadmin@example.com',
            'current_password' => 'StrongPass123',
        ]))->assertSessionHasErrors(['email']);

        $this->assertSame('admin@reservepro.test', $user->fresh()->email);
    }

    public function test_all_staff_roles_need_a_valid_current_password_to_change_password(): void
    {
        foreach (['admin', 'front_desk', 'security'] as $slug) {
            $user = $this->staff($slug);
            $payload = $this->profilePayload($user, [
                'password' => 'NewStrongPass123',
                'password_confirmation' => 'NewStrongPass123',
            ]);

            $this->actingAs($user)->put(route('profile.update'), $payload)
                ->assertSessionHasErrors(['current_password']);
            $this->actingAs($user)->put(route('profile.update'), [
                ...$payload,
                'current_password' => 'StrongPass123',
            ])->assertSessionHasNoErrors();

            $this->assertTrue(Hash::check('NewStrongPass123', $user->fresh()->password));
        }
    }

    public function test_invalid_profile_fields_and_photo_are_rejected(): void
    {
        $user = $this->staff('security');

        $this->actingAs($user)->put(route('profile.update'), $this->profilePayload($user, [
            'name' => '123',
            'phone' => 'not a phone',
            'avatar' => UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
        ]))->assertSessionHasErrors(['name', 'phone', 'avatar']);

        $this->assertSame('Demo Staff', $user->fresh()->name);
    }
}
