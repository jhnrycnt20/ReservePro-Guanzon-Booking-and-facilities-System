<?php

namespace App\Notifications;

use App\Models\IncidentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidentInProgressNotification extends Notification
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

        return [
            'title' => 'Report in progress',
            'message' => sprintf('Report %s is now in progress.', $num),
            'type' => 'report',
            'id' => $this->report->id,
            'number' => $num,
            'report_id' => $this->report->id,
            'report_number' => $num,
        ];
    }
}
