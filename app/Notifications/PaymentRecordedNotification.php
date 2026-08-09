<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRecordedNotification extends Notification
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

        return [
            'title' => 'Payment recorded',
            'message' => sprintf('A payment for booking %s was recorded and awaits verification.', $num),
            'type' => 'booking',
            'id' => $this->booking->id,
            'number' => $num,
            'booking_id' => $this->booking->id,
            'booking_number' => $num,
        ];
    }
}
