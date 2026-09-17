<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffPaymentRecordedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public float $amount,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->booking->short_number;

        return [
            'title' => 'Payment received',
            'message' => sprintf(
                '₱%s recorded for %s (%s).',
                number_format($this->amount, 2),
                $this->booking->guest_name,
                $num
            ),
            'type' => 'booking',
            'id' => $this->booking->id,
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
        ];
    }
}
