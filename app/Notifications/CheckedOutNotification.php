<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CheckedOutNotification extends Notification
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
        return [
            'type' => 'checked_out',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'message' => "Check-out completed for reservation {$this->booking->booking_number}.",
        ];
    }
}
