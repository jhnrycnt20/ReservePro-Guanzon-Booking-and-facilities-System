<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Guest;
use App\Models\Promo;
use App\Models\Role;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PromoRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function guestAndAccommodation(): array
    {
        $role = Role::query()->firstOrCreate(['slug' => 'guest'], ['name' => 'Guest']);
        $user = User::query()->create([
            'role_id' => $role->id,
            'name' => 'Promo Guest',
            'email' => 'promo-guest@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
        $guest = Guest::query()->create(['user_id' => $user->id, 'contact_number' => '09171234567']);
        $type = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);
        $accommodation = Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'Promo Room',
            'number' => 'P-1',
            'capacity' => 2,
            'rate' => 1000,
            'status' => 'available',
            'is_active' => true,
        ]);

        return [$user, $guest, $accommodation];
    }

    private function singleUsePromo(): Promo
    {
        return Promo::query()->create([
            'code' => 'LASTUSE',
            'name' => 'Last use',
            'discount_percent' => 20,
            'applies_to_all' => true,
            'is_active' => true,
            'usage_limit' => 1,
        ]);
    }

    private function reservationData(Accommodation $accommodation, int $daysFromNow): array
    {
        return [
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDays($daysFromNow)->toDateString(),
            'check_out_date' => today()->addDays($daysFromNow + 1)->toDateString(),
            'adults' => 1,
            'children' => 0,
        ];
    }

    public function test_last_use_is_consumed_by_reservation_and_next_reservation_is_rejected(): void
    {
        [$user, , $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            ...$this->reservationData($accommodation, 1),
            'promo_code' => $promo->code,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('guest.bookings.store'), [
            ...$this->reservationData($accommodation, 3),
            'promo_code' => $promo->code,
        ])->assertSessionHasErrors(['promo_code']);

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame(1, $promo->fresh()->used_count);
    }

    public function test_last_use_is_consumed_when_applied_to_existing_booking(): void
    {
        [$user, $guest, $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();
        $bookingService = app(BookingService::class);
        $first = $bookingService->createReservation($this->reservationData($accommodation, 1), $guest, $user);
        $second = $bookingService->createReservation($this->reservationData($accommodation, 3), $guest, $user);

        $this->actingAs($user)->post(route('guest.bookings.apply_promo', $first), [
            'promo_code' => $promo->code,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('guest.bookings.apply_promo', $second), [
            'promo_code' => $promo->code,
        ])->assertSessionHasErrors(['promo_code']);

        $this->assertSame(1, $promo->fresh()->used_count);
        $this->assertNull($second->fresh()->promo_id);
    }

    public function test_guest_can_preview_a_promo_using_the_stored_rate(): void
    {
        [$user, , $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();

        $this->actingAs($user)->get(route('guest.bookings.create', [
            'accommodation_id' => $accommodation->id,
        ]))->assertOk()->assertSee('data-rp-promo-input', false);

        $this->actingAs($user)->postJson(route('guest.promos.validate'), [
            'promo_code' => $promo->code,
            'accommodation_id' => $accommodation->id,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(3)->toDateString(),
            'rate' => 1,
        ])->assertOk()
            ->assertJsonPath('original_total', 2000)
            ->assertJsonPath('discount_amount', 400)
            ->assertJsonPath('promo_total', 1600);

        $this->assertSame(0, $promo->fresh()->used_count);
    }

    public function test_cancelling_a_booking_restores_its_promo_use_once(): void
    {
        [$user, $guest, $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();
        $service = app(BookingService::class);
        $booking = $service->createReservation([
            ...$this->reservationData($accommodation, 1),
            'promo_code' => $promo->code,
        ], $guest, $user);

        $service->cancel($booking, $user);

        $this->assertSame(0, $promo->fresh()->used_count);
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);

        try {
            $service->cancel($booking, $user);
            $this->fail('Cancelling the same booking twice should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
        $this->assertSame(0, $promo->fresh()->used_count);

        $replacement = $service->createReservation([
            ...$this->reservationData($accommodation, 3),
            'promo_code' => $promo->code,
        ], $guest, $user);

        $this->assertSame($promo->id, $replacement->promo_id);
        $this->assertSame(1, $promo->fresh()->used_count);
    }

    public function test_rejecting_a_pending_booking_restores_its_promo_use(): void
    {
        [$user, $guest, $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();
        $service = app(BookingService::class);
        $booking = $service->createReservation([
            ...$this->reservationData($accommodation, 1),
            'promo_code' => $promo->code,
        ], $guest, $user);
        $booking->update(['status' => BookingStatus::Pending]);

        $service->reject($booking->fresh(), $user, 'Unavailable');

        $this->assertSame(0, $promo->fresh()->used_count);
        $this->assertSame(BookingStatus::Rejected, $booking->fresh()->status);
    }

    public function test_booking_items_sum_to_the_discounted_total_for_both_redemption_paths(): void
    {
        [$user, $guest, $accommodation] = $this->guestAndAccommodation();
        $promo = $this->singleUsePromo();
        $promo->update(['usage_limit' => 2]);
        $service = app(BookingService::class);

        $atCreation = $service->createReservation([
            ...$this->reservationData($accommodation, 1),
            'promo_code' => $promo->code,
        ], $guest, $user);
        $afterCreation = $service->createReservation($this->reservationData($accommodation, 3), $guest, $user);
        $afterCreation = $service->applyPromo($afterCreation, $promo->code);

        foreach ([$atCreation, $afterCreation] as $booking) {
            $this->assertCount(2, $booking->items);
            $this->assertEqualsWithDelta(
                (float) $booking->total_amount,
                (float) $booking->items->sum('total'),
                0.001
            );
            $this->assertEquals(-200, (float) $booking->items->last()->total);
        }
    }
}
