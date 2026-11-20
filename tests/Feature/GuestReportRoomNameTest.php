<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\IncidentReport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestReportRoomNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_reports_shows_the_booked_room_name_instead_of_the_report_title(): void
    {
        $role = Role::query()->create(['name' => 'Guest', 'slug' => 'guest']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'user_id' => $user->id,
            'contact_number' => '09171234567',
        ]);
        $type = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);
        $room = Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'AC Room with Videoke 1',
            'number' => 'RM-AC-VK-01',
            'capacity' => 4,
            'rate' => 2500,
            'status' => 'available',
            'is_active' => true,
        ]);
        $booking = Booking::query()->create([
            'booking_number' => 'BK-REPORT-ROOM',
            'guest_id' => $guest->id,
            'accommodation_id' => $room->id,
            'guest_name' => $user->name,
            'contact_number' => $guest->contact_number,
            'email' => $user->email,
            'check_in_date' => today()->addDay()->toDateString(),
            'check_out_date' => today()->addDays(2)->toDateString(),
        ]);
        IncidentReport::query()->create([
            'report_number' => 'IR-REPORT-ROOM',
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
            'report_type' => 'incident',
            'title' => 'test',
            'description' => 'test',
            'location' => 'Room',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('guest.incidents.index'))
            ->assertOk()
            ->assertSee('AC Room with Videoke 1')
            ->assertDontSee('test');
    }
}
