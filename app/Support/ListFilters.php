<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class ListFilters
{
    public static function applyBookingSearch(Builder $query, ?string $term): Builder
    {
        $q = trim((string) $term);
        if ($q === '') {
            return $query;
        }

        $qCore = preg_replace('/^BK-/i', '', $q) ?: $q;

        return $query->where(function (Builder $builder) use ($q, $qCore) {
            $builder->where('booking_number', 'like', "%{$q}%")
                ->orWhere('booking_number', 'like', "%{$qCore}%")
                ->orWhere('guest_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('contact_number', 'like', "%{$q}%")
                ->orWhereHas('accommodation', function (Builder $accommodationQuery) use ($q) {
                    $accommodationQuery->where('name', 'like', "%{$q}%");
                });
        });
    }

    public static function applyIncidentSearch(Builder $query, ?string $term): Builder
    {
        $q = trim((string) $term);
        if ($q === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($q) {
            $builder->where('report_number', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%")
                ->orWhere('location', 'like', "%{$q}%")
                ->orWhereHas('guest.user', function (Builder $userQuery) use ($q) {
                    $userQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
        });
    }
}
