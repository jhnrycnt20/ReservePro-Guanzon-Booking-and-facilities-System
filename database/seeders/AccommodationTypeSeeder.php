<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use Illuminate\Database\Seeder;

class AccommodationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Room', 'slug' => 'room', 'description' => 'Standard and deluxe guest rooms'],
            ['name' => 'Cottage', 'slug' => 'cottage', 'description' => 'Open-air cottages near amenities'],
            ['name' => 'Villa', 'slug' => 'villa', 'description' => 'Private villas for families and groups'],
        ];

        foreach ($types as $type) {
            AccommodationType::query()->updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
