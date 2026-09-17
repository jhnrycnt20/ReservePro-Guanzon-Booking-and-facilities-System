<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffGuestCheckedInNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->booking->short_number;

        return [
            'title' => 'Guest checked in',
            'message' => sprintf(
                '%s checked in for %s (%s).',
                $this->booking->guest_name,
                $this->booking->accommodation?->name ?? 'accommodation',
                $num
            ),
            'type' => 'checked_in',
            'id' => $this->booking->id,
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
        ];
    }
}
