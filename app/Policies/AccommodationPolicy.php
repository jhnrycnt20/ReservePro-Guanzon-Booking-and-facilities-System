<?php

namespace App\Policies;

use App\Models\Accommodation;
use App\Models\User;

class AccommodationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Accommodation $accommodation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Accommodation $accommodation): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Accommodation $accommodation): bool
    {
        return $user->isAdmin();
    }
}
