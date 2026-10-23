<?php

namespace App\Notifications;

use App\Models\IncidentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidentReportSubmittedNotification extends Notification
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

        return [
            'title' => 'New incident report',
            'message' => sprintf('%s submitted report %s: %s', $guest, $num, $title),
            'type' => 'report',
            'id' => $this->report->id,
            'number' => $num,
            'report_id' => $this->report->id,
            'report_number' => $num,
            'incident_report_id' => $this->report->id,
        ];
    }
}
