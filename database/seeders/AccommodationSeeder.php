<?php

namespace Database\Seeders;

use App\Enums\AccommodationStatus;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Room', 'slug' => 'room', 'description' => 'Standard resort rooms'],
            ['name' => 'Cottage', 'slug' => 'cottage', 'description' => 'Family cottages'],
            ['name' => 'Villa', 'slug' => 'villa', 'description' => 'Premium villas'],
            ['name' => 'Cabin', 'slug' => 'cabin', 'description' => 'Cozy cabins'],
        ] as $type) {
            AccommodationType::query()->updateOrCreate(['slug' => $type['slug']], $type);
        }

        foreach ([
            ['name' => 'WiFi', 'icon' => 'wifi'],
            ['name' => 'Air Conditioning', 'icon' => 'snow'],
            ['name' => 'Private Bathroom', 'icon' => 'droplet'],
            ['name' => 'Kitchenette', 'icon' => 'cup-hot'],
            ['name' => 'TV', 'icon' => 'tv'],
            ['name' => 'Parking', 'icon' => 'p-circle'],
        ] as $amenity) {
            Amenity::query()->updateOrCreate(
                ['name' => $amenity['name']],
                $amenity + ['description' => null]
            );
        }

        $amenityIds = Amenity::query()->pluck('id');
        $typeIds = AccommodationType::query()->pluck('id', 'slug');

        foreach ([
            ['name' => 'Garden Room 101', 'number' => 'R-101', 'type' => 'room', 'capacity' => 2, 'rate' => 2500, 'image' => 'images/rooms/ocean-view-room.png'],
            ['name' => 'Garden Room 102', 'number' => 'R-102', 'type' => 'room', 'capacity' => 2, 'rate' => 2500, 'image' => 'images/rooms/ocean-view-room.png'],
            ['name' => 'Family Cottage A', 'number' => 'C-A1', 'type' => 'cottage', 'capacity' => 6, 'rate' => 4500, 'image' => 'images/rooms/bamboo-lounge.png'],
            ['name' => 'Family Cottage B', 'number' => 'C-B1', 'type' => 'cottage', 'capacity' => 8, 'rate' => 5500, 'image' => 'images/rooms/blue-cabana.png'],
            ['name' => 'Sunset Villa', 'number' => 'V-01', 'type' => 'villa', 'capacity' => 10, 'rate' => 12000, 'image' => 'images/rooms/bamboo-lounge.png'],
            ['name' => 'Pine Cabin 1', 'number' => 'K-01', 'type' => 'cabin', 'capacity' => 4, 'rate' => 3800, 'image' => 'images/rooms/garden-cabin.png'],
        ] as $item) {
            $accommodation = Accommodation::query()->updateOrCreate(
                ['number' => $item['number']],
                [
                    'accommodation_type_id' => $typeIds[$item['type']],
                    'name' => $item['name'],
                    'description' => $item['name'].' with resort amenities and scenic surroundings.',
                    'capacity' => $item['capacity'],
                    'rate' => $item['rate'],
                    'image' => $item['image'],
                    'status' => AccommodationStatus::Available,
                    'is_active' => true,
                ]
            );

            $accommodation->amenities()->sync(
                $amenityIds->shuffle()->take(min(4, $amenityIds->count()))->values()->all()
            );
        }
    }
}
