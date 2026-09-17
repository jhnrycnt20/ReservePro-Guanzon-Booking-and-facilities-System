<?php

namespace App\Services;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\User;
use App\Notifications\CheckedInNotification;
use App\Notifications\StaffGuestCheckedInNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService,
        protected PaymentService $paymentService,
    ) {
    }

    public function checkIn(Booking $booking, User $staff, ?string $notes = null): CheckIn
    {
        if ($booking->status !== BookingStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => 'Only approved bookings can be checked in.',
            ]);
        }

        if ($booking->checkIn) {
            throw ValidationException::withMessages([
                'booking_id' => 'This booking is already checked in.',
            ]);
        }

        $booking = $this->paymentService->recalculateBalances($booking);

        if (! $booking->isFullyPaid()) {
            $balance = number_format((float) $booking->remaining_balance, 2);
            throw ValidationException::withMessages([
                'payment' => "Full payment (remaining ₱{$balance}) is required before check-in.",
            ]);
        }

        if (! $booking->checkInWindowOpen()) {
            throw ValidationException::withMessages([
                'check_in_date' => 'Check-in opens at '.config('resort.check_in_time', '14:00').' on the arrival date.',
            ]);
        }

        return DB::transaction(function () use ($booking, $staff, $notes) {
            $checkIn = CheckIn::query()->create([
                'booking_id' => $booking->id,
                'staff_id' => $staff->id,
                'checked_in_at' => now(),
                'notes' => $notes,
            ]);

            $old = $booking->toArray();

            $booking->update([
                'status' => BookingStatus::CheckedIn,
            ]);

            $booking->accommodation->update([
                'status' => AccommodationStatus::Occupied,
            ]);

            $this->auditService->log('booking.checked_in', $booking, $old, $booking->fresh()->toArray(), $staff);

            if ($booking->guest?->user) {
                $this->notificationService->notify($booking->guest->user, new CheckedInNotification($booking));
            }

            $booking->loadMissing('accommodation');
            $this->notificationService->notifyFrontDesk(new StaffGuestCheckedInNotification($booking->fresh()));

            return $checkIn->fresh(['booking', 'staff']);
        });
    }
}
