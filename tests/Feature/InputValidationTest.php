<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => 'Test User',
            'email' => uniqid('user', true).'@gmail.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    private function accommodation(): Accommodation
    {
        $type = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);

        return Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'Ocean Room',
            'number' => 'R-1',
            'capacity' => 4,
            'rate' => 1000,
            'status' => 'available',
            'is_active' => true,
        ]);
    }

    public function test_guest_booking_rejects_invalid_guest_counts_instead_of_silently_correcting_them(): void
    {
        $user = $this->userWithRole('guest');
        Guest::query()->create(['user_id' => $user->id, 'contact_number' => '09171234567']);
        $accommodation = $this->accommodation();

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
            'adults' => '-3',
            'children' => 'not-a-number',
        ])->assertSessionHasErrors(['adults', 'children']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_guest_booking_rejects_a_malformed_accommodation_identifier(): void
    {
        $user = $this->userWithRole('guest');
        Guest::query()->create(['user_id' => $user->id, 'contact_number' => '09171234567']);

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            'accommodation_id' => ['invalid'],
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
            'adults' => 1,
            'children' => 0,
        ])->assertSessionHasErrors(['accommodation_id']);
    }

    public function test_booking_rejects_a_total_that_would_overflow_money_columns(): void
    {
        $user = $this->userWithRole('guest');
        Guest::query()->create(['user_id' => $user->id, 'contact_number' => '09171234567']);
        $accommodation = $this->accommodation();
        $accommodation->update(['rate' => '9000000000.00']);

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(3)->toDateString(),
            'adults' => 1,
            'children' => 0,
        ])->assertSessionHasErrors(['check_out_date']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_walk_in_rejects_fractional_cents_and_requires_gcash_reference(): void
    {
        $staff = $this->userWithRole('front_desk');
        $accommodation = $this->accommodation();
        $payload = [
            'guest_name' => 'Walk In Guest',
            'contact_number' => '09171234567',
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'payment_method' => 'gcash',
            'payment_amount' => '100.001',
        ];

        $this->actingAs($staff)->post(route('front_desk.walkins.store'), $payload)
            ->assertSessionHasErrors(['payments.0.amount', 'payments.0.reference_number']);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_cannot_deactivate_or_demote_their_own_account(): void
    {
        $admin = $this->userWithRole('admin');
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $guestRole->id,
            'is_active' => 0,
        ])->assertSessionHasErrors(['role_id', 'is_active']);

        $this->assertSame($admin->role_id, $admin->fresh()->role_id);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_still_update_their_own_profile_without_changing_access(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => 'Updated Admin',
            'email' => strtoupper($admin->email),
            'role_id' => (string) $admin->role_id,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Updated Admin', $admin->fresh()->name);
        $this->assertSame($admin->email, $admin->fresh()->email);
    }

    public function test_settings_reject_invalid_times(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'resort_name' => 'Guanzon',
            'resort_email' => 'hello@example.com',
            'resort_phone' => '09171234567',
            'currency' => 'PHP',
            'check_in_time' => '25:75',
            'check_out_time' => '12:00',
        ])->assertSessionHasErrors(['check_in_time']);

        $this->assertDatabaseCount('system_settings', 0);
    }

    public function test_promo_code_is_checked_for_uniqueness_after_normalization(): void
    {
        $admin = $this->userWithRole('admin');
        DB::table('promos')->insert([
            'code' => 'SUMMER20',
            'discount_percent' => 20,
            'applies_to_all' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.promos.store'), [
            'code' => 'summer20',
            'discount_percent' => 20,
            'applies_to_all' => 1,
        ])->assertSessionHasErrors(['code']);

        $this->assertDatabaseCount('promos', 1);
    }

    public function test_promo_can_still_be_created_without_a_supplied_code(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.promos.store'), [
            'name' => 'Test promo',
            'discount_percent' => '10.50',
            'applies_to_all' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('promos', 1);
        $this->assertNotEmpty(DB::table('promos')->value('code'));
    }

    public function test_promo_name_is_required(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.promos.store'), [
            'name' => '',
            'discount_percent' => 10,
            'applies_to_all' => 1,
        ])->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('promos', 0);
    }

    public function test_promo_accepts_the_current_minute_from_datetime_local(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.promos.store'), [
            'name' => 'Timed promo',
            'discount_percent' => 10,
            'applies_to_all' => 1,
            'starts_at' => now()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('promos', 1);
    }

    public function test_admin_cannot_set_promo_limit_below_existing_uses(): void
    {
        $admin = $this->userWithRole('admin');
        $promo = \App\Models\Promo::query()->create([
            'code' => 'USEDTWICE',
            'name' => 'Used promo',
            'discount_percent' => 20,
            'applies_to_all' => true,
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 2,
        ]);

        $this->actingAs($admin)->put(route('admin.promos.update', $promo), [
            'name' => $promo->name,
            'code' => $promo->code,
            'discount_percent' => 20,
            'applies_to_all' => 1,
            'usage_limit' => 1,
            'is_active' => 1,
        ])->assertSessionHasErrors(['usage_limit']);

        $this->assertSame(5, $promo->fresh()->usage_limit);
    }

    public function test_admin_email_uniqueness_is_case_insensitive(): void
    {
        $admin = $this->userWithRole('admin');
        $existing = $this->userWithRole('guest');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Another Guest',
            'email' => strtoupper($existing->email),
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'role_id' => $existing->role_id,
        ])->assertSessionHasErrors(['email']);
    }

    public function test_admin_must_choose_a_supported_role(): void
    {
        $admin = $this->userWithRole('admin');
        $unsupportedRole = Role::query()->create(['name' => 'Other', 'slug' => 'other']);
        $payload = [
            'name' => 'New User',
            'email' => 'new.user@gmail.com',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ];

        $this->actingAs($admin)->post(route('admin.users.store'), $payload)
            ->assertSessionHasErrors(['role_id']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            ...$payload,
            'role_id' => $unsupportedRole->id,
        ])->assertSessionHasErrors(['role_id']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_created_guest_requires_a_valid_phone_and_normalizes_it(): void
    {
        $admin = $this->userWithRole('admin');
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $payload = [
            'name' => 'Guest User',
            'email' => 'guest.user@gmail.com',
            'role_id' => $guestRole->id,
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ];

        $this->actingAs($admin)->post(route('admin.users.store'), $payload)
            ->assertSessionHasErrors(['phone']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            ...$payload,
            'phone' => 'not a phone',
        ])->assertSessionHasErrors(['phone']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            ...$payload,
            'phone' => '+63 (917) 123-4567',
        ])->assertSessionHasNoErrors();

        $guest = User::query()->where('email', $payload['email'])->firstOrFail();
        $this->assertSame('09171234567', $guest->phone);
        $this->assertSame('09171234567', $guest->guest->contact_number);
    }

    public function test_changing_a_user_to_guest_also_requires_a_contact_number(): void
    {
        $admin = $this->userWithRole('admin');
        $staff = $this->userWithRole('front_desk');
        $guestRole = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $payload = [
            'name' => $staff->name,
            'email' => $staff->email,
            'role_id' => $guestRole->id,
            'is_active' => 1,
        ];

        $this->actingAs($admin)->put(route('admin.users.update', $staff), $payload)
            ->assertSessionHasErrors(['phone']);
        $this->assertSame('front_desk', $staff->fresh()->role->slug);

        $this->actingAs($admin)->put(route('admin.users.update', $staff), [
            ...$payload,
            'phone' => '09171234567',
        ])->assertSessionHasNoErrors();

        $this->assertSame('guest', $staff->fresh()->role->slug);
        $this->assertSame('09171234567', $staff->guest->contact_number);
    }

    public function test_add_user_normalizes_names_and_reports_confirmation_errors_on_the_confirmation_field(): void
    {
        $admin = $this->userWithRole('admin');
        $payload = [
            'name' => '  Ana   María  ',
            'email' => 'anamaria@gmail.com',
            'role_id' => $admin->role_id,
            'password' => 'StrongPass123',
            'password_confirmation' => 'DifferentPass123',
        ];

        $this->actingAs($admin)->post(route('admin.users.store'), $payload)
            ->assertSessionHasErrors(['password_confirmation']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            ...$payload,
            'name' => '---',
            'password_confirmation' => 'StrongPass123',
        ])->assertSessionHasErrors(['name']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            ...$payload,
            'password_confirmation' => 'StrongPass123',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Ana María', User::query()->where('email', $payload['email'])->value('name'));
    }

    public function test_deleted_user_email_gets_a_clear_duplicate_error(): void
    {
        $admin = $this->userWithRole('admin');
        $deletedUser = $this->userWithRole('guest');
        $deletedUser->delete();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Another User',
            'email' => $deletedUser->email,
            'role_id' => $admin->role_id,
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertSessionHasErrors([
            'email' => 'This email is already associated with an account, including deleted accounts. Use a different email.',
        ]);
    }

    public function test_accommodation_type_rejects_duplicate_generated_slug(): void
    {
        $admin = $this->userWithRole('admin');
        AccommodationType::query()->create(['name' => 'Family Room', 'slug' => 'family-room']);

        $this->actingAs($admin)->post(route('admin.types.store'), [
            'name' => 'Family  Room',
        ])->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('accommodation_types', 1);
    }

    public function test_accommodation_page_rejects_invalid_date_query(): void
    {
        $accommodation = $this->accommodation();

        $this->get(route('accommodations.show', [
            'accommodation' => $accommodation,
            'check_in' => 'not-a-date',
            'check_out' => 'tomorrow',
        ]))->assertSessionHasErrors(['check_in', 'check_out']);
    }

    public function test_guest_cannot_attach_another_guests_booking_to_an_incident(): void
    {
        $owner = $this->userWithRole('guest');
        $reporter = $this->userWithRole('guest');
        $ownerGuest = Guest::query()->create(['user_id' => $owner->id, 'contact_number' => '09171234567']);
        Guest::query()->create(['user_id' => $reporter->id, 'contact_number' => '09171234568']);
        $accommodation = $this->accommodation();
        $booking = Booking::query()->create([
            'booking_number' => 'BK-TEST',
            'guest_id' => $ownerGuest->id,
            'accommodation_id' => $accommodation->id,
            'guest_name' => $owner->name,
            'contact_number' => '09171234567',
            'email' => $owner->email,
            'check_in_date' => today()->toDateString(),
            'check_out_date' => today()->addDay()->toDateString(),
        ]);

        $this->actingAs($reporter)->post(route('guest.incidents.store'), [
            'booking_id' => $booking->id,
            'report_type' => 'incident',
            'description' => 'A detailed report of an issue.',
        ])->assertSessionHasErrors(['booking_id']);

        $this->assertDatabaseCount('incident_reports', 0);
    }
}
