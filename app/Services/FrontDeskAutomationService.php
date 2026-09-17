<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FrontDeskAutomationService
{
    public function __construct(
        protected CheckInService $checkInService,
        protected CheckOutService $checkOutService,
        protected PaymentService $paymentService,
    ) {
    }

    public function runDueActions(?User $actor = null): void
    {
        $actor = $actor ?? $this->resolveStaffActor();

        Booking::query()
            ->where('status', BookingStatus::Approved)
            ->whereDate('check_in_date', '<=', today())
            ->orderBy('id')
            ->each(fn (Booking $booking) => $this->tryAutoCheckIn($booking, $actor));

        Booking::query()
            ->where('status', BookingStatus::CheckedIn)
            ->checkOutDue()
            ->orderBy('id')
            ->each(fn (Booking $booking) => $this->tryAutoCheckOut($booking, $actor));
    }

    public function syncBooking(Booking $booking, ?User $actor = null): void
    {
        $actor = $actor ?? $this->resolveStaffActor();
        $booking = $booking->fresh();

        if ($booking->status === BookingStatus::Approved) {
            $this->tryAutoCheckIn($booking, $actor);
        }

        $booking = $booking->fresh();

        if ($booking->status === BookingStatus::CheckedIn) {
            $this->tryAutoCheckOut($booking, $actor);
        }
    }

    public function tryAutoCheckIn(Booking $booking, User $staff): void
    {
        if ($booking->status !== BookingStatus::Approved) {
            return;
        }

        if ($booking->check_in_date->toDateString() > today()->toDateString()) {
            return;
        }

        $booking = $this->paymentService->recalculateBalances($booking);

        if (! $booking->isFullyPaid() || ! $booking->checkInWindowOpen()) {
            return;
        }

        try {
            $this->checkInService->checkIn($booking, $staff, 'Automatic check-in');
        } catch (ValidationException $exception) {
            Log::debug('Auto check-in skipped', [
                'booking_id' => $booking->id,
                'messages' => $exception->errors(),
            ]);
        }
    }

    public function tryAutoCheckOut(Booking $booking, User $staff): void
    {
        if ($booking->status !== BookingStatus::CheckedIn) {
            return;
        }

        if (! $booking->checkOutDue()) {
            return;
        }

        $booking = $this->paymentService->recalculateBalances($booking);

        if ((float) $booking->remaining_balance > 0) {
            return;
        }

        try {
            $this->checkOutService->checkOut($booking, $staff, 0, 'Automatic check-out');
        } catch (ValidationException $exception) {
            Log::debug('Auto check-out skipped', [
                'booking_id' => $booking->id,
                'messages' => $exception->errors(),
            ]);
        }
    }

    protected function resolveStaffActor(): User
    {
        $staff = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('slug', 'front_desk'))
            ->first()
            ?? User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($q) => $q->where('slug', 'admin'))
                ->first();

        if ($staff) {
            return $staff;
        }

        return User::query()->where('is_active', true)->firstOrFail();
    }
}
