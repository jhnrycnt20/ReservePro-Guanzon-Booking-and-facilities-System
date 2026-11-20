<?php

namespace Tests\Feature;

use App\Enums\IncidentStatus;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\IncidentReport;
use Tests\TestCase;

class GuestReportProgressLabelTest extends TestCase
{
    public function test_progress_heading_shows_the_booked_accommodation_unit(): void
    {
        $accommodation = new Accommodation([
            'name' => 'Garden Villa',
            'number' => 'GV-02',
        ]);
        $booking = new Booking();
        $booking->setRelation('accommodation', $accommodation);
        $report = new IncidentReport(['status' => IncidentStatus::Pending]);
        $report->setRelation('booking', $booking);

        $html = view('partials.guest-report-progress', compact('report'))->render();

        $this->assertStringContainsString('Garden Villa · Unit GV-02', $html);
        $this->assertStringNotContainsString('Submitted — waiting for review', $html);
    }
}
