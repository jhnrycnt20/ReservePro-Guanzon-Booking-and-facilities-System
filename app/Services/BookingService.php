<?php

namespace App\Services;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\User;
use App\Notifications\BookingApprovedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingRejectedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected AuditService $auditService,
        protected NotificationService $notificationService,
        protected PromoService $promoService,
    ) {
    }

    public function generateBookingNumber(): string
    {
        // Short, easy-to-read codes like BK-7K2M (no date clutter).
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $number = 'BK-'.$suffix;
        } while (Booking::withTrashed()->where('booking_number', $number)->exists());

        return $number;
    }

    public function calculateTotal(Accommodation $accommodation, string $checkIn, string $checkOut): array
    {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();
        $nights = max(1, $checkInDate->diffInDays($checkOutDate));
        $rate = (float) $accommodation->rate;
        $total = round($nights * $rate, 2);

        return [
            'nights' => $nights,
            'rate' => $rate,
            'total' => $total,
        ];
    }

    public function createReservation(array $data, Guest $guest, ?User $createdBy = null, bool $isWalkIn = false): Booking
    {
        $accommodation = Accommodation::query()->findOrFail($data['accommodation_id']);

        if (! $this->availabilityService->isAvailable(
            $accommodation->id,
            $data['check_in_date'],
            $data['check_out_date']
        )) {
            throw ValidationException::withMessages([
                'accommodation_id' => 'The selected accommodation is not available for the chosen dates.',
            ]);
        }

        $adults = max(0, (int) ($data['adults'] ?? 1));
        $children = max(0, (int) ($data['children'] ?? 0));
        // Always derive from adults + children so the separate guests field cannot bypass capacity.
        $numberOfGuests = $adults + $children;

        if ($numberOfGuests < 1) {
            throw ValidationException::withMessages([
                'adults' => 'At least one guest is required.',
            ]);
        }

        if ($numberOfGuests > $accommodation->capacity) {
            throw ValidationException::withMessages([
                'adults' => "Total guests ({$numberOfGuests}) exceeds this accommodation's capacity of {$accommodation->capacity}.",
            ]);
        }

        $totals = $this->calculateTotal(
            $accommodation,
            $data['check_in_date'],
            $data['check_out_date']
        );

        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = $this->promoService->findValidForAccommodation(
                (string) $data['promo_code'],
                (int) $accommodation->id
            );
            $totals = $this->promoService->applyToTotals($totals, $promo);
        }

        return DB::transaction(function () use ($data, $guest, $createdBy, $isWalkIn, $accommodation, $adults, $children, $numberOfGuests, $totals, $promo) {
            $booking = Booking::query()->create([
                'booking_number' => $this->generateBookingNumber(),
                'guest_id' => $guest->id,
                'accommodation_id' => $accommodation->id,
                'guest_name' => $data['guest_name'] ?? $guest->user->name,
                'contact_number' => $data['contact_number'] ?? $guest->contact_number,
                'email' => $data['email'] ?? $guest->user->email,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'adults' => $adults,
                'children' => $children,
                'number_of_guests' => $numberOfGuests,
                'special_requests' => $data['special_requests'] ?? null,
                'status' => BookingStatus::Approved,
                'approved_by' => $createdBy?->id,
                'approved_at' => now(),
                'original_amount' => $totals['original_total'] ?? $totals['total'],
                'discount_amount' => $totals['discount_amount'] ?? 0,
                'discount_percent' => $totals['discount_percent'] ?? null,
                'promo_id' => $promo?->id,
                'promo_code' => $promo?->code,
                'total_amount' => $totals['total'],
                'paid_amount' => 0,
                'remaining_balance' => $totals['total'],
                'is_walk_in' => $isWalkIn,
                'created_by' => $createdBy?->id,
            ]);

            $accommodation->update([
                'status' => AccommodationStatus::Reserved,
            ]);

            $booking->items()->create([
                'description' => "{$accommodation->name} ({$totals['nights']} night(s))"
                    .($promo ? " · Promo {$promo->code} ({$promo->discount_percent}% off)" : ''),
                'quantity' => $totals['nights'],
                'unit_price' => $totals['rate'],
                'total' => $totals['total'],
            ]);

            if ($promo) {
                $this->promoService->markUsed($promo);
            }

            $this->auditService->log('booking.created', $booking, null, $booking->toArray(), $createdBy);

            if ($guest->user) {
                $this->notificationService->notify($guest->user, new BookingCreatedNotification($booking));
            }

            return $booking->fresh(['items', 'accommodation', 'guest.user', 'promo']);
        });
    }

    public function applyPromo(Booking $booking, string $promoCode): Booking
    {
        if ($booking->promo_id || ((float) $booking->discount_amount) > 0) {
            throw ValidationException::withMessages([
                'promo_code' => 'A promo has already been applied to this booking.',
            ]);
        }

        if ((float) $booking->paid_amount > 0) {
            throw ValidationException::withMessages([
                'promo_code' => 'Promo codes can only be applied before any payment is recorded.',
            ]);
        }

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'promo_code' => 'Promo codes can only be applied to pending or approved bookings.',
            ]);
        }

        $promo = $this->promoService->findValidForAccommodation($promoCode, (int) $booking->accommodation_id);
        $original = (float) ($booking->original_amount ?? $booking->total_amount);
        $discount = $promo->discountAmount($original);
        $final = max(0, round($original - $discount, 2));

        return DB::transaction(function () use ($booking, $promo, $original, $discount, $final) {
            $old = $booking->toArray();

            $booking->update([
                'promo_id' => $promo->id,
                'promo_code' => $promo->code,
                'original_amount' => $original,
                'discount_amount' => $discount,
                'discount_percent' => $promo->discount_percent,
                'total_amount' => $final,
                'remaining_balance' => max(0, round($final - (float) $booking->paid_amount, 2)),
            ]);

            $this->promoService->markUsed($promo);
            $this->auditService->log('booking.promo_applied', $booking, $old, $booking->fresh()->toArray());

            return $booking->fresh(['promo', 'accommodation']);
        });
    }

    public function approve(Booking $booking, User $approver): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending bookings can be approved.',
            ]);
        }

        if (! $this->availabilityService->isAvailable(
            $booking->accommodation_id,
            $booking->check_in_date,
            $booking->check_out_date,
            $booking->id
        )) {
            throw ValidationException::withMessages([
                'accommodation_id' => 'Cannot approve: accommodation is no longer available for these dates.',
            ]);
        }

        return DB::transaction(function () use ($booking, $approver) {
            $old = $booking->toArray();

            $booking->update([
                'status' => BookingStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $booking->accommodation->update([
                'status' => AccommodationStatus::Reserved,
            ]);

            $this->auditService->log('booking.approved', $booking, $old, $booking->fresh()->toArray(), $approver);

            if ($booking->guest?->user) {
                $this->notificationService->notify($booking->guest->user, new BookingApprovedNotification($booking));
            }

            return $booking->fresh();
        });
    }

    public function reject(Booking $booking, User $rejector, string $reason): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending bookings can be rejected.',
            ]);
        }

        return DB::transaction(function () use ($booking, $rejector, $reason) {
            $old = $booking->toArray();

            $booking->update([
                'status' => BookingStatus::Rejected,
                'rejection_reason' => $reason,
                'rejected_by' => $rejector->id,
                'rejected_at' => now(),
            ]);

            $this->auditService->log('booking.rejected', $booking, $old, $booking->fresh()->toArray(), $rejector);

            if ($booking->guest?->user) {
                $this->notificationService->notify($booking->guest->user, new BookingRejectedNotification($booking));
            }

            return $booking->fresh();
        });
    }

    public function cancel(Booking $booking, ?User $actor = null): Booking
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only pending or approved bookings can be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($booking, $actor) {
            $old = $booking->toArray();

            $booking->update([
                'status' => BookingStatus::Cancelled,
            ]);

            if ($booking->accommodation && $booking->accommodation->status === AccommodationStatus::Reserved) {
                $stillReserved = Booking::query()
                    ->where('accommodation_id', $booking->accommodation_id)
                    ->where('id', '!=', $booking->id)
                    ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::CheckedIn->value])
                    ->exists();

                if (! $stillReserved) {
                    $booking->accommodation->update([
                        'status' => AccommodationStatus::Available,
                    ]);
                }
            }

            $this->auditService->log('booking.cancelled', $booking, $old, $booking->fresh()->toArray(), $actor);

            if ($booking->guest?->user) {
                $this->notificationService->notify($booking->guest->user, new BookingCancelledNotification($booking));
            }

            return $booking->fresh();
        });
    }
}
