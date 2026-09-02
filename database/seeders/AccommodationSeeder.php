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
        $this->call([
            AccommodationTypeSeeder::class,
            AmenitySeeder::class,
        ]);

        $amenities = Amenity::query()->pluck('id', 'name');
        $typeIds = AccommodationType::query()->pluck('id', 'slug');

        $listings = [
            [
                'number' => 'RM-AC-01',
                'name' => 'AC Room w/out Videoke',
                'type' => 'room',
                'capacity' => 4,
                'rate' => 1500,
                'image' => 'images/rooms/ocean-view-room.png',
                'description' => 'Overnight room at Guanzon Beach. Day use and night use: ₱1,500. Complimentary for 2 persons, maximum 4 persons.',
                'amenities' => ['Air Conditioning', 'Private Bathroom', 'Television', 'Wi-Fi'],
            ],
            [
                'number' => 'RM-AC-VK',
                'name' => 'AC Room with Videoke',
                'type' => 'room',
                'capacity' => 8,
                'rate' => 2500,
                'image' => 'images/rooms/ocean-view-room.png',
                'description' => 'Overnight room with videoke at Guanzon Beach. Day use and night use: ₱2,500. Complimentary for 2 persons, maximum 8 persons.',
                'amenities' => ['Air Conditioning', 'Private Bathroom', 'Television', 'Videoke', 'Wi-Fi'],
            ],
            [
                'number' => 'BH-01',
                'name' => 'Beach House',
                'type' => 'beach-house',
                'capacity' => 8,
                'rate' => 4500,
                'image' => 'images/rooms/bamboo-lounge.png',
                'description' => 'Beach house at Guanzon Beach. Day use and night use: ₱4,500. Complimentary for 5 persons, maximum 8 persons.',
                'amenities' => ['Private Bathroom', 'Television', 'Kitchenette', 'Parking'],
            ],
            [
                'number' => 'SUITE-01',
                'name' => 'Suite Room',
                'type' => 'suite',
                'capacity' => 12,
                'rate' => 7500,
                'image' => 'images/rooms/ocean-view-room.png',
                'description' => 'Suite room at Guanzon Beach. Day use and night use: ₱7,500. Complimentary for 2 persons, maximum 12 persons.',
                'amenities' => ['Air Conditioning', 'Private Bathroom', 'Television', 'Wi-Fi', 'Parking'],
            ],
            [
                'number' => 'VILLA-01',
                'name' => 'Villa',
                'type' => 'villa',
                'capacity' => 15,
                'rate' => 8000,
                'image' => 'images/rooms/bamboo-lounge.png',
                'description' => 'Villa at Guanzon Beach. Day use and night use: ₱8,000. Complimentary for 5 persons, maximum 15 persons.',
                'amenities' => ['Air Conditioning', 'Private Bathroom', 'Television', 'Kitchenette', 'Parking', 'Pool Access'],
            ],
            [
                'number' => 'COT-NVK',
                'name' => 'Cottage w/out Videoke',
                'type' => 'cottage',
                'capacity' => 8,
                'rate' => 1500,
                'image' => 'images/rooms/garden-cabin.png',
                'description' => 'Day use ₱1,500 (8:00 AM–5:00 PM). Night use ₱1,500 (check-in 6:00 PM, check-out 6:00 AM).',
                'amenities' => ['Parking', 'Pool Access'],
            ],
            [
                'number' => 'COT-VK',
                'name' => 'Cottage with Videoke',
                'type' => 'cottage',
                'capacity' => 10,
                'rate' => 2500,
                'image' => 'images/rooms/blue-cabana.png',
                'description' => 'Day use ₱2,500 (8:00 AM–5:00 PM). Night use ₱2,500 (check-in 6:00 PM, check-out 6:00 AM). Includes videoke.',
                'amenities' => ['Videoke', 'Parking', 'Pool Access'],
            ],
            [
                'number' => 'CAB-01',
                'name' => 'Cabana',
                'type' => 'cabana',
                'capacity' => 6,
                'rate' => 800,
                'image' => 'images/rooms/blue-cabana.png',
                'description' => 'Day use ₱800 (8:00 AM–5:00 PM). Night use ₱800 (check-in 2:00 PM, check-out 12:00 MN).',
                'amenities' => ['Pool Access', 'Parking'],
            ],
            [
                'number' => 'PAY-01',
                'name' => 'Payag',
                'type' => 'payag',
                'capacity' => 6,
                'rate' => 850,
                'image' => 'images/rooms/garden-cabin.png',
                'description' => 'Day use ₱850 (8:00 AM–5:00 PM). Night use ₱850 (check-in 6:00 PM, check-out 6:00 AM).',
                'amenities' => ['Parking', 'Pool Access'],
            ],
            [
                'number' => 'TBL-01',
                'name' => 'Table',
                'type' => 'table',
                'capacity' => 4,
                'rate' => 500,
                'image' => 'images/rooms/garden-cabin.png',
                'description' => 'Day use ₱500 (8:00 AM–5:00 PM). Night use ₱500 (check-in 6:00 PM, check-out 6:00 AM).',
                'amenities' => ['Parking'],
            ],
        ];

        $activeNumbers = collect($listings)->pluck('number')->all();

        Accommodation::query()
            ->whereNotIn('number', $activeNumbers)
            ->update(['is_active' => false]);

        foreach ($listings as $item) {
            $accommodation = Accommodation::query()->updateOrCreate(
                ['number' => $item['number']],
                [
                    'accommodation_type_id' => $typeIds[$item['type']],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'capacity' => $item['capacity'],
                    'rate' => $item['rate'],
                    'image' => $item['image'],
                    'status' => AccommodationStatus::Available,
                    'is_active' => true,
                ]
            );

            $amenityIds = collect($item['amenities'])
                ->map(fn (string $name) => $amenities[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $accommodation->amenities()->sync($amenityIds);
        }
    }
}
