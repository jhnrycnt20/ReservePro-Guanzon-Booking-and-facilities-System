<?php

namespace Database\Seeders;

use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncidentReportSeeder extends Seeder
{
    public function run(): void
    {
        $guest = Guest::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'guest@reservepro.test'))
            ->firstOrFail();

        $securityGuard = User::query()
            ->where('email', 'security@reservepro.test')
            ->firstOrFail();

        $frontDeskStaff = User::query()
            ->where('email', 'frontdesk@reservepro.test')
            ->firstOrFail();

        $bookingId = Booking::query()
            ->where('guest_id', $guest->id)
            ->latest()
            ->value('id');

        $samples = [
            [
                'report_number' => 'IR-DEMO-PENDING',
                'report_type' => IncidentType::Incident,
                'title' => 'Slippery floor near the pool',
                'description' => 'Water has collected beside the pool entrance and may cause someone to slip.',
                'location' => 'Pool entrance',
                'status' => IncidentStatus::Pending,
            ],
            [
                'report_number' => 'IR-DEMO-VERIFIED',
                'report_type' => IncidentType::BrokenAmenity,
                'title' => 'Broken shower fixture',
                'description' => 'The shower handle is loose and cannot control the water properly.',
                'location' => 'Room 102 bathroom',
                'status' => IncidentStatus::Verified,
                'security_guard_id' => $securityGuard->id,
                'investigation_notes' => 'Confirmed the shower handle is damaged and requires replacement.',
            ],
            [
                'report_number' => 'IR-DEMO-INVALID',
                'report_type' => IncidentType::Complaint,
                'title' => 'Reported noise from an empty room',
                'description' => 'Loud voices were reportedly heard from the room late at night.',
                'location' => 'Room 205',
                'status' => IncidentStatus::Invalid,
                'security_guard_id' => $securityGuard->id,
                'invalid_reason' => 'The room was vacant and no noise was found during the security inspection.',
                'closed_at' => now()->subHours(10),
            ],
            [
                'report_number' => 'IR-DEMO-IN-PROGRESS',
                'report_type' => IncidentType::Maintenance,
                'title' => 'Air conditioner not cooling',
                'description' => 'The air conditioner runs but the room remains warm.',
                'location' => 'Room 108',
                'status' => IncidentStatus::InProgress,
                'security_guard_id' => $securityGuard->id,
                'investigation_notes' => 'Verified that the unit is running without producing cold air.',
                'front_desk_staff_id' => $frontDeskStaff->id,
                'resolution_action' => 'Requested maintenance inspection',
                'resolution_notes' => 'Maintenance has been notified and is checking the cooling unit.',
            ],
            [
                'report_number' => 'IR-DEMO-RESOLVED',
                'report_type' => IncidentType::BrokenAmenity,
                'title' => 'Television remote not working',
                'description' => 'The television remote does not respond even after changing batteries.',
                'location' => 'Room 110',
                'status' => IncidentStatus::Resolved,
                'security_guard_id' => $securityGuard->id,
                'investigation_notes' => 'Confirmed the remote was defective.',
                'front_desk_staff_id' => $frontDeskStaff->id,
                'resolution_action' => 'Replaced defective remote',
                'resolution_notes' => 'A working replacement remote was delivered and tested with the guest.',
                'resolved_at' => now()->subHours(6),
            ],
            [
                'report_number' => 'IR-DEMO-CLOSED',
                'report_type' => IncidentType::Complaint,
                'title' => 'Missing bath towels',
                'description' => 'The room did not have the expected set of bath towels after check-in.',
                'location' => 'Room 104',
                'status' => IncidentStatus::Closed,
                'security_guard_id' => $securityGuard->id,
                'investigation_notes' => 'Confirmed the room was missing two bath towels.',
                'front_desk_staff_id' => $frontDeskStaff->id,
                'resolution_action' => 'Delivered replacement towels',
                'resolution_notes' => 'Housekeeping delivered a complete towel set and the guest confirmed receipt.',
                'resolved_at' => now()->subDay(),
                'closed_at' => now()->subHours(20),
            ],
        ];

        foreach ($samples as $sample) {
            IncidentReport::query()->updateOrCreate(
                ['report_number' => $sample['report_number']],
                array_merge([
                    'guest_id' => $guest->id,
                    'booking_id' => $bookingId,
                    'security_guard_id' => null,
                    'investigation_notes' => null,
                    'investigation_photo' => null,
                    'invalid_reason' => null,
                    'front_desk_staff_id' => null,
                    'resolution_notes' => null,
                    'resolution_action' => null,
                    'resolved_at' => null,
                    'closed_at' => null,
                ], $sample)
            );
        }
    }
}
