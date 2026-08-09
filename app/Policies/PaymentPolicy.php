<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isFrontDesk() || $user->isGuestRole();
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin() || $user->isFrontDesk()) {
            return true;
        }

        return $user->isGuestRole() && $user->guest?->id === $payment->booking?->guest_id;
    }

    public function create(User $user): bool
    {
        return $user->isGuestRole() || $user->isFrontDesk() || $user->isAdmin();
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->isAdmin() || $user->isFrontDesk();
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->isAdmin() || $user->isFrontDesk();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
