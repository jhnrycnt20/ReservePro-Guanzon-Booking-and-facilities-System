<?php

namespace App\Notifications;

use App\Models\IncidentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidentVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public IncidentReport $report)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $num = $this->report->report_number;
        $guest = $this->report->guest?->user?->name ?? 'Guest';
        $title = $this->report->title ?: 'Incident report';

        $isStaff = method_exists($notifiable, 'isFrontDesk')
            && ($notifiable->isFrontDesk() || $notifiable->isAdmin() || $notifiable->isSecurity());

        $message = $isStaff
            ? sprintf('Verified report %s from %s is ready for front desk: %s', $num, $guest, $title)
            : sprintf('Report %s was verified and forwarded to front desk.', $num);

        return [
            'title' => 'Report verified',
            'message' => $message,
            'type' => 'report',
            'id' => $this->report->id,
            'number' => $num,
            'report_id' => $this->report->id,
            'report_number' => $num,
            'incident_report_id' => $this->report->id,
        ];
    }
}
