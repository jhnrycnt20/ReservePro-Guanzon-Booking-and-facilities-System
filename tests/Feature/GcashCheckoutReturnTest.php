<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GcashCheckoutReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_gcash_return_redirects_to_the_booking(): void
    {
        $role = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'user_id' => $user->id,
            'contact_number' => '09171234567',
        ]);
        $type = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);
        $accommodation = Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'Ocean Room',
            'number' => 'R-1',
            'capacity' => 2,
            'rate' => 1000,
            'status' => 'available',
            'is_active' => true,
        ]);
        $booking = Booking::query()->create([
            'booking_number' => 'BK-GCASH-RETURN',
            'guest_id' => $guest->id,
            'accommodation_id' => $accommodation->id,
            'guest_name' => $user->name,
            'contact_number' => $guest->contact_number,
            'email' => $user->email,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
        ]);
        $payment = new Payment(['status' => PaymentStatus::Verified]);

        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('syncPendingGcashCheckout')
            ->once()
            ->with(Mockery::on(fn (Booking $value): bool => $value->is($booking)))
            ->andReturn($payment);
        $this->app->instance(PaymentService::class, $paymentService);

        $this->actingAs($user)
            ->get(route('guest.payments.gcash.return', [
                'booking' => $booking,
                'status' => 'success',
            ]))
            ->assertRedirect(route('guest.bookings.show', $booking))
            ->assertSessionHas('success', 'Your GCash payment was confirmed.');
    }
}
