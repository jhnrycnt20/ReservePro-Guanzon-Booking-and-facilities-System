<?php

namespace App\Helpers;

use App\Enums\UserRole;
use App\Models\User;

class RoleRedirect
{
    public static function dashboardRoute(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return route('login');
        }

        return match ($user->role?->slug) {
            UserRole::Admin->value => route('admin.dashboard'),
            UserRole::FrontDesk->value => route('front_desk.dashboard'),
            UserRole::Security->value => route('security.dashboard'),
            UserRole::Guest->value => route('guest.dashboard'),
            default => route('home'),
        };
    }

    public static function dashboardPath(?User $user = null): string
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return '/login';
        }

        return match ($user->role?->slug) {
            UserRole::Admin->value => '/admin/dashboard',
            UserRole::FrontDesk->value => '/front-desk/dashboard',
            UserRole::Security->value => '/security/dashboard',
            UserRole::Guest->value => '/guest/dashboard',
            default => '/home',
        };
    }
}
