<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            'WiFi',
            'Air Conditioning',
            'Parking',
            'Coffee Machine',
            'Projector',
            'Whiteboard',
            'Printer',
            'Standing Desk',
            'Meeting Room',
            'Kitchen',
            'Shower',
            'Locker',
        ];

        foreach ($amenities as $amenityName) {
            Amenity::firstOrCreate(['amenity_name' => $amenityName]);
        }

        $this->command->info('✅ Created ' . count($amenities) . ' amenities');
    }
}
