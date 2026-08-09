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
        return [
            'type' => 'incident_submitted',
            'incident_report_id' => $this->report->id,
            'report_number' => $this->report->report_number,
            'message' => "New incident report {$this->report->report_number}: {$this->report->title}",
        ];
    }
}
