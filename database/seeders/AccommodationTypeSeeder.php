<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use Illuminate\Database\Seeder;

class AccommodationTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Room', 'slug' => 'room', 'description' => 'Overnight rooms at Guanzon Beach'],
            ['name' => 'Beach House', 'slug' => 'beach-house', 'description' => 'Beach house accommodations'],
            ['name' => 'Suite', 'slug' => 'suite', 'description' => 'Suite rooms for groups'],
            ['name' => 'Villa', 'slug' => 'villa', 'description' => 'Private villas'],
            ['name' => 'Cottage', 'slug' => 'cottage', 'description' => 'Day and night use cottages'],
            ['name' => 'Cabana', 'slug' => 'cabana', 'description' => 'Cabana day and night use'],
            ['name' => 'Payag', 'slug' => 'payag', 'description' => 'Payag day and night use'],
            ['name' => 'Table', 'slug' => 'table', 'description' => 'Table day and night use'],
        ] as $type) {
            AccommodationType::query()->updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
