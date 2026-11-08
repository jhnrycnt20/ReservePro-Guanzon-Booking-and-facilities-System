<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
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
        $num = $this->booking->booking_number;
        $isPending = $this->booking->status === \App\Enums\BookingStatus::Pending;

        return [
            'title' => $isPending ? 'Payment required' : 'Reservation confirmed',
            'message' => $isPending
                ? sprintf('Your reservation %s is awaiting payment. Complete payment to confirm your stay.', $num)
                : sprintf('Your reservation %s is confirmed and reserved.', $num),
            'type' => 'booking',
            'id' => $this->booking->id,
            'number' => $num,
            'booking_id' => $this->booking->id,
            'booking_number' => $num,
        ];
    }
}
