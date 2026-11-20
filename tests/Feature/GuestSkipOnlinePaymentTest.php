<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GuestSkipOnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_billing_draft_does_not_offer_skip_online_payment(): void
    {
        Notification::fake();

        $role = Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Guest']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Guest User',
            'email' => 'guest-skip-payment@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'user_id' => $user->id,
            'contact_number' => '09171234567',
        ]);
        $type = AccommodationType::query()->create([
            'name' => 'Room',
            'slug' => 'room',
        ]);
        $accommodation = Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'Guest Room',
            'number' => 'RM-AC-VK-01',
            'capacity' => 2,
            'rate' => 4500,
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDays(3)->toDateString(),
            'check_out_date' => today()->addDays(4)->toDateString(),
            'adults' => 1,
            'children' => 0,
        ])->assertRedirect(route('guest.payments.draft'));

        $this->actingAs($user)->get(route('guest.payments.draft'))
            ->assertOk()
            ->assertSee('Continue to GCash')
            ->assertDontSee('Skip online checkout')
            ->assertDontSee('rp-payment-skip-actions', false);
    }
}
