<?php

namespace Tests\Feature;

use App\Enums\AccommodationStatus;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Promo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccommodationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_hide_maintenance_rooms_but_keep_available_family_units(): void
    {
        $type = AccommodationType::query()->create([
            'name' => 'Room',
            'slug' => 'room',
        ]);
        $maintenanceRoom = $this->createAccommodation(
            $type,
            'Maintenance AC Room 1',
            'RM-AC-VK-01',
            AccommodationStatus::Maintenance
        );
        $availableRoom = $this->createAccommodation(
            $type,
            'Available AC Room 2',
            'RM-AC-VK-02',
            AccommodationStatus::Available
        );
        Promo::query()->create([
            'code' => 'ROOMSALE',
            'name' => 'Room sale',
            'discount_percent' => 10,
            'applies_to_all' => true,
            'is_active' => true,
        ]);

        $this->get(route('accommodations.browse'))
            ->assertOk()
            ->assertSee('Available AC Room')
            ->assertDontSee('Maintenance AC Room')
            ->assertDontSee('under maintenance');

        $this->get('/')
            ->assertOk()
            ->assertSee('Available AC Room')
            ->assertDontSee('Maintenance AC Room')
            ->assertDontSee('under maintenance');

        $this->get(route('offers'))
            ->assertOk()
            ->assertSee('Available AC Room 2')
            ->assertDontSee('Maintenance AC Room 1');

        $this->get(route('accommodations.show', $availableRoom))
            ->assertOk()
            ->assertSee('Available AC Room')
            ->assertDontSee('RM-AC-VK-01', false);

        $this->get(route('accommodations.show', $maintenanceRoom))
            ->assertRedirect(route('accommodations.browse'))
            ->assertSessionHas('error', 'Sorry, this accommodation is currently unavailable.');
    }

    private function createAccommodation(
        AccommodationType $type,
        string $name,
        string $number,
        AccommodationStatus $status
    ): Accommodation {
        return Accommodation::query()->create([
            'accommodation_type_id' => $type->id,
            'name' => $name,
            'number' => $number,
            'capacity' => 4,
            'rate' => 1500,
            'status' => $status,
            'is_active' => true,
        ]);
    }
}
