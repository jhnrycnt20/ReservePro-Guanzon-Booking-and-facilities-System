<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isFrontDesk() || $user->isGuestRole();
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin() || $user->isFrontDesk()) {
            return true;
        }

        return $user->isGuestRole() && $user->guest?->id === $booking->guest_id;
    }

    public function create(User $user): bool
    {
        return $user->isGuestRole() || $user->isFrontDesk() || $user->isAdmin();
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->isFrontDesk();
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->isFrontDesk();
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $user->isFrontDesk();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isAdmin() || $user->isFrontDesk()) {
            return true;
        }

        return $user->isGuestRole() && $user->guest?->id === $booking->guest_id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }
}
