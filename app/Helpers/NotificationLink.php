<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationLink
{
    public static function url(DatabaseNotification $notification, ?User $user = null): string
    {
        $user ??= auth()->user();
        $data = $notification->data ?? [];
        $type = (string) ($data['type'] ?? '');

        $bookingId = $data['booking_id'] ?? ((in_array($type, ['booking', 'checked_in', 'checked_out'], true)) ? ($data['id'] ?? null) : null);
        $reportId = $data['report_id']
            ?? $data['incident_report_id']
            ?? ((str_contains($type, 'incident') || $type === 'report') ? ($data['id'] ?? null) : null);

        if ($bookingId) {
            return self::bookingUrl((int) $bookingId, $user);
        }

        if ($reportId) {
            return self::reportUrl((int) $reportId, $user);
        }

        return route('notifications.index');
    }

    protected static function bookingUrl(int $bookingId, ?User $user): string
    {
        if ($user?->isGuestRole()) {
            return route('guest.bookings.show', $bookingId);
        }

        if ($user?->isFrontDesk()) {
            return route('front_desk.reservations.show', $bookingId);
        }

        if ($user?->isAdmin()) {
            return route('admin.dashboard');
        }

        return route('notifications.index');
    }

    protected static function reportUrl(int $reportId, ?User $user): string
    {
        if ($user?->isGuestRole()) {
            return route('guest.incidents.show', $reportId);
        }

        if ($user?->isSecurity()) {
            return route('security.incidents.show', $reportId);
        }

        if ($user?->isFrontDesk()) {
            return route('front_desk.incidents.show', $reportId);
        }

        if ($user?->isAdmin()) {
            return route('admin.reports.show', $reportId);
        }

        return route('notifications.index');
    }
}
