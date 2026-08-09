<?php

namespace App\Services;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\User;
use App\Notifications\CheckedOutNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckOutService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService,
        protected PaymentService $paymentService,
    ) {
    }

    public function checkOut(
        Booking $booking,
        User $staff,
        float $additionalCharges = 0,
        ?string $notes = null
    ): CheckOut {
        if ($booking->status !== BookingStatus::CheckedIn) {
            throw ValidationException::withMessages([
                'status' => 'Only checked-in bookings can be checked out.',
            ]);
        }

        if ($booking->checkOut) {
            throw ValidationException::withMessages([
                'booking_id' => 'This booking is already checked out.',
            ]);
        }

        $additionalCharges = max(0, round($additionalCharges, 2));

        return DB::transaction(function () use ($booking, $staff, $additionalCharges, $notes) {
            if ($additionalCharges > 0) {
                $booking->update([
                    'total_amount' => round((float) $booking->total_amount + $additionalCharges, 2),
                ]);

                $booking->items()->create([
                    'description' => 'Additional charges at check-out',
                    'quantity' => 1,
                    'unit_price' => $additionalCharges,
                    'total' => $additionalCharges,
                ]);
            }

            $booking = $this->paymentService->recalculateBalances($booking->fresh());

            if ((float) $booking->remaining_balance > 0) {
                throw ValidationException::withMessages([
                    'payment' => 'Outstanding balance must be settled before check-out.',
                ]);
            }

            $checkOut = CheckOut::query()->create([
                'booking_id' => $booking->id,
                'staff_id' => $staff->id,
                'checked_out_at' => now(),
                'additional_charges' => $additionalCharges,
                'final_balance' => 0,
                'notes' => $notes,
            ]);

            $old = $booking->toArray();

            $booking->update([
                'status' => BookingStatus::CheckedOut,
            ]);

            $booking->accommodation->update([
                'status' => AccommodationStatus::Available,
            ]);

            $this->auditService->log('booking.checked_out', $booking, $old, $booking->fresh()->toArray(), $staff);

            if ($booking->guest?->user) {
                $this->notificationService->notify($booking->guest->user, new CheckedOutNotification($booking));
            }

            return $checkOut->fresh(['booking', 'staff']);
        });
    }
}
