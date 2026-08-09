<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Accommodation;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AvailabilityService
{
    /**
     * Blocking statuses prevent double booking.
     * Rejected / cancelled / checked_out do NOT block.
     */
    public function isAvailable(
        int $accommodationId,
        CarbonInterface|string $checkIn,
        CarbonInterface|string $checkOut,
        ?int $excludeBookingId = null
    ): bool {
        $checkIn = Carbon::parse($checkIn)->startOfDay();
        $checkOut = Carbon::parse($checkOut)->startOfDay();

        if ($checkOut->lte($checkIn)) {
            return false;
        }

        $accommodation = Accommodation::query()->find($accommodationId);

        if (! $accommodation || ! $accommodation->is_active) {
            return false;
        }

        if (in_array($accommodation->status?->value ?? $accommodation->status, [
            'maintenance',
            'inactive',
        ], true)) {
            return false;
        }

        return ! $this->hasOverlappingBooking($accommodationId, $checkIn, $checkOut, $excludeBookingId);
    }

    public function hasOverlappingBooking(
        int $accommodationId,
        CarbonInterface|string $checkIn,
        CarbonInterface|string $checkOut,
        ?int $excludeBookingId = null
    ): bool {
        $checkIn = Carbon::parse($checkIn)->toDateString();
        $checkOut = Carbon::parse($checkOut)->toDateString();

        $query = Booking::query()
            ->where('accommodation_id', $accommodationId)
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Approved->value,
                BookingStatus::CheckedIn->value,
            ])
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Overlap: existing.check_in < new.check_out AND existing.check_out > new.check_in
                $q->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }
}
