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

class FrontDeskPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->create(['name' => ucfirst(str_replace('_', ' ', $slug)), 'slug' => $slug]);

        return User::query()->create([
            'role_id' => $role->id,
            'name' => ucfirst($slug).' User',
            'email' => $slug.'@example.com',
            'password' => 'StrongPass123',
            'is_active' => true,
        ]);
    }

    private function guest(): Guest
    {
        $user = $this->userWithRole('guest');

        return Guest::query()->create([
            'user_id' => $user->id,
            'contact_number' => '09171234567',
        ]);
    }

    public function test_front_desk_incidents_show_ten_rows_per_page(): void
    {
        $frontDesk = $this->userWithRole('front_desk');
        $guest = $this->guest();

        foreach (range(1, 11) as $number) {
            IncidentReport::query()->create([
                'report_number' => "IR-FD-{$number}",
                'guest_id' => $guest->id,
                'report_type' => 'incident',
                'title' => "Incident {$number}",
                'description' => "Incident report {$number}",
                'status' => 'in_progress',
            ]);
        }

        $this->actingAs($frontDesk)
            ->get(route('front_desk.incidents.index'))
            ->assertOk()
            ->assertViewHas('reports', fn ($reports) => $reports->perPage() === 10
                && $reports->count() === 10
                && $reports->total() === 11);
    }

    public function test_front_desk_reservations_show_ten_rows_per_page(): void
    {
        $frontDesk = $this->userWithRole('front_desk');
        $guest = $this->guest();
        $guestUser = $guest->user;
        $type = AccommodationType::query()->create(['name' => 'Room', 'slug' => 'room']);
        $accommodation = Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => 'Test Room',
            'number' => '1',
            'capacity' => 2,
            'rate' => 1000,
            'status' => 'available',
            'is_active' => true,
        ]);

        foreach (range(1, 11) as $number) {
            Booking::query()->create([
                'booking_number' => "BK-FD-{$number}",
                'guest_id' => $guest->id,
                'accommodation_id' => $accommodation->id,
                'guest_name' => $guestUser->name,
                'contact_number' => $guest->contact_number,
                'email' => $guestUser->email,
                'check_in_date' => today()->addDays(3)->toDateString(),
                'check_out_date' => today()->addDays(4)->toDateString(),
                'status' => 'pending',
                'total_amount' => 1000,
                'paid_amount' => 500,
                'remaining_balance' => 500,
            ]);
        }

        $this->actingAs($frontDesk)
            ->get(route('front_desk.reservations.index'))
            ->assertOk()
            ->assertViewHas('bookings', fn ($bookings) => $bookings->perPage() === 10
                && $bookings->count() === 10
                && $bookings->total() === 11);
    }
}
