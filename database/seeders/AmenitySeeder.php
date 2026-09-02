<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Wi-Fi', 'icon' => 'wifi', 'description' => 'Complimentary wireless internet'],
            ['name' => 'Air Conditioning', 'icon' => 'snowflake', 'description' => 'Climate-controlled rooms'],
            ['name' => 'Private Bathroom', 'icon' => 'droplet', 'description' => 'Ensuite bathroom'],
            ['name' => 'Television', 'icon' => 'tv', 'description' => 'Cable TV'],
            ['name' => 'Mini Fridge', 'icon' => 'fridge', 'description' => 'In-room refrigerator'],
            ['name' => 'Pool Access', 'icon' => 'water', 'description' => 'Access to resort pool'],
            ['name' => 'Kitchenette', 'icon' => 'utensils', 'description' => 'Basic cooking facilities'],
            ['name' => 'Parking', 'icon' => 'car', 'description' => 'Complimentary parking'],
            ['name' => 'Videoke', 'icon' => 'mic', 'description' => 'Karaoke / videoke available'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::query()->updateOrCreate(['name' => $amenity['name']], $amenity);
        }
    }
}
