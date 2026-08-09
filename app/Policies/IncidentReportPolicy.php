<?php

namespace App\Policies;

use App\Models\IncidentReport;
use App\Models\User;

class IncidentReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isFrontDesk() || $user->isSecurity() || $user->isGuestRole();
    }

    public function view(User $user, IncidentReport $incidentReport): bool
    {
        if ($user->isAdmin() || $user->isFrontDesk() || $user->isSecurity()) {
            return true;
        }

        return $user->isGuestRole() && $user->guest?->id === $incidentReport->guest_id;
    }

    public function create(User $user): bool
    {
        return $user->isGuestRole() || $user->isFrontDesk() || $user->isAdmin();
    }

    public function investigate(User $user, IncidentReport $incidentReport): bool
    {
        return $user->isSecurity() || $user->isAdmin();
    }

    public function resolve(User $user, IncidentReport $incidentReport): bool
    {
        return $user->isFrontDesk() || $user->isAdmin();
    }

    public function update(User $user, IncidentReport $incidentReport): bool
    {
        return $user->isAdmin() || $user->isFrontDesk() || $user->isSecurity();
    }

    public function delete(User $user, IncidentReport $incidentReport): bool
    {
        return $user->isAdmin();
    }
}
