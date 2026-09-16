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
                'image' => 'images/rooms/cabana/06-bedroom-ac.png',
                'gallery' => [
                    'images/rooms/cabana/05-bedroom.png',
                    'images/rooms/cabana/07-bedroom-towels.png',
                    'images/rooms/cabana/08-bathroom.png',
                    'images/rooms/cabana/09-bathroom-vanity.png',
                ],
                'description' => 'Overnight AC room at Guanzon Beach without videoke. Day use and night use: ₱1,500. Complimentary for 2 persons, maximum 4 persons.',
                'amenities' => ['Air Conditioning', 'Private Bathroom', 'Television', 'Wi-Fi'],
            ],
            [
                'number' => 'RM-AC-VK',
                'name' => 'AC Room with Videoke',
                'type' => 'room',
                'capacity' => 8,
                'rate' => 2500,
                'image' => 'images/rooms/suite/06-karaoke-tv.png',
                'gallery' => [
                    'images/rooms/cabana/06-bedroom-ac.png',
                    'images/rooms/suite/08-living-area.png',
                    'images/rooms/cabana/08-bathroom.png',
                    'images/rooms/suite/05-outdoor-dining.png',
                ],
                'description' => 'Overnight AC room with videoke at Guanzon Beach. Day use and night use: ₱2,500. Complimentary for 2 persons, maximum 8 persons.',
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
                'capacity' => 5,
                'rate' => 2500,
                'image' => 'images/rooms/suite/01-exterior-row.png',
                'gallery' => [
                    'images/rooms/suite/02-sea-view-walkway.png',
                    'images/rooms/suite/03-balcony-sea-view.png',
                    'images/rooms/suite/04-entrance.png',
                    'images/rooms/suite/05-outdoor-dining.png',
                    'images/rooms/suite/06-karaoke-tv.png',
                    'images/rooms/suite/07-bedroom-sea-view.png',
                    'images/rooms/suite/08-living-area.png',
                    'images/rooms/suite/09-living-desk.png',
                    'images/rooms/suite/10-bathroom.png',
                ],
                'description' => 'Suite Room at Guanzon Beach. Rate ₱2,500. Bed good for 5 pax. Includes table and chair, optional videoke, mini refrigerator, griller, toilet, aircon, cable TV, hot shower, shampoo and soap, water and coffee, no corkage on foods and drinks, and sea view.',
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
                'name' => 'Open Cottage',
                'type' => 'cottage',
                'capacity' => 12,
                'rate' => 1700,
                'image' => 'images/rooms/cottage/01-exterior-front.png',
                'gallery' => [
                    'images/rooms/cottage/02-exterior-side.png',
                    'images/rooms/cottage/03-interior-long-table.png',
                    'images/rooms/cottage/04-interior-seating.png',
                    'images/rooms/cottage/05-bamboo-lounge.png',
                    'images/rooms/cottage/06-picnic-tables.png',
                ],
                'description' => 'Guanzon Beach Open Cottage. Day use ₱1,700 (8:00 AM–5:00 PM). Night use ₱1,700 (6:00 PM–6:00 AM). Videoke optional for ₱1,000.',
                'amenities' => ['Parking', 'Pool Access'],
            ],
            [
                'number' => 'COT-VK',
                'name' => 'Open Cottage with Videoke',
                'type' => 'cottage',
                'capacity' => 12,
                'rate' => 2700,
                'image' => 'images/rooms/cottage/01-exterior-front.png',
                'gallery' => [
                    'images/rooms/cottage/02-exterior-side.png',
                    'images/rooms/cottage/03-interior-long-table.png',
                    'images/rooms/cottage/04-interior-seating.png',
                    'images/rooms/cottage/05-bamboo-lounge.png',
                    'images/rooms/cottage/06-picnic-tables.png',
                ],
                'description' => 'Guanzon Beach Open Cottage with videoke. Day use ₱2,700 (8:00 AM–5:00 PM). Night use ₱2,700 (6:00 PM–6:00 AM). Includes videoke (₱1,000 add-on).',
                'amenities' => ['Videoke', 'Parking', 'Pool Access'],
            ],
            [
                'number' => 'CAB-01',
                'name' => 'Cabana',
                'type' => 'cabana',
                'capacity' => 4,
                'rate' => 2500,
                'image' => 'images/rooms/cabana/02-exterior-unit.png',
                'gallery' => [
                    'images/rooms/cabana/01-exterior-row.png',
                    'images/rooms/cabana/03-exterior-porch.png',
                    'images/rooms/cabana/04-exterior-evening.png',
                    'images/rooms/cabana/05-bedroom.png',
                    'images/rooms/cabana/06-bedroom-ac.png',
                    'images/rooms/cabana/07-bedroom-towels.png',
                    'images/rooms/cabana/08-bathroom.png',
                    'images/rooms/cabana/09-bathroom-vanity.png',
                    'images/rooms/cabana/10-outdoor-grill.png',
                ],
                'description' => 'Promo rate ₱2,500 (good for 4 pax). Includes aircon, Smart TV, hot and cold shower, toiletries, complimentary coffee & water, electric kettle, pay WiFi, and balcony. No pets. No visitors.',
                'amenities' => ['Pool Access', 'Parking', 'Air Conditioning', 'WiFi'],
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
            $accommodation = Accommodation::withTrashed()->firstOrNew(['number' => $item['number']]);
            if ($accommodation->trashed()) {
                $accommodation->restore();
            }

            $accommodation->fill([
                'accommodation_type_id' => $typeIds[$item['type']],
                'name' => $item['name'],
                'description' => $item['description'],
                'capacity' => $item['capacity'],
                'rate' => $item['rate'],
                'image' => $item['image'],
                'gallery' => $item['gallery'] ?? null,
                'status' => AccommodationStatus::Available,
                'is_active' => true,
            ]);
            $accommodation->save();

            $amenityIds = collect($item['amenities'])
                ->map(fn (string $name) => $amenities[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $accommodation->amenities()->sync($amenityIds);
        }
    }
}
