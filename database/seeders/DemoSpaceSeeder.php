<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venue;
use App\Models\Space;
use App\Models\SpaceType;
use App\Models\Amenity;

class DemoSpaceSeeder extends Seeder
{
    /**
     * Seed demo spaces for each venue.
     */
    public function run(): void
    {
        // Ensure space types exist
        $spaceTypes = [
            'Meeting Room',
            'Co-working Space',
            'Private Office',
            'Event Hall',
            'Conference Room'
        ];

        foreach ($spaceTypes as $typeName) {
            SpaceType::firstOrCreate(['type_name' => $typeName]);
        }

        $meetingRoomType = SpaceType::where('type_name', 'Meeting Room')->first();
        $coworkingType = SpaceType::where('type_name', 'Co-working Space')->first();
        $privateOfficeType = SpaceType::where('type_name', 'Private Office')->first();

        // Get amenities for random assignment
        $allAmenities = Amenity::all()->pluck('id')->toArray();

        // Get all venues
        $venues = Venue::all();

        foreach ($venues as $venue) {
            // Venue 1: 2 spaces
            if ($venue->id == 3 || $venue->name == 'Coworking HUST') {
                Space::firstOrCreate(
                    ['name' => 'Phòng họp 201', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $meetingRoomType->id,
                        'capacity' => 8,
                        'price_per_hour' => 100000,
                        'price_per_day' => 700000,
                        'price_per_month' => 15000000,
                        'open_hour' => '08:00:00',
                        'close_hour' => '22:00:00'
                    ]
                );

                Space::firstOrCreate(
                    ['name' => 'Coworking Space A', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $coworkingType->id,
                        'capacity' => 20,
                        'price_per_hour' => 50000,
                        'price_per_day' => 350000,
                        'price_per_month' => 8000000,
                        'open_hour' => '08:00:00',
                        'close_hour' => '22:00:00'
                    ]
                );
            }

            // Venue 2: 3 spaces
            if ($venue->id == 4 || $venue->name == 'Sky Office Tower') {
                Space::firstOrCreate(
                    ['name' => 'Executive Meeting Room', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $meetingRoomType->id,
                        'capacity' => 12,
                        'price_per_hour' => 200000,
                        'price_per_day' => 1400000,
                        'price_per_month' => 30000000,
                        'open_hour' => '08:00:00',
                        'close_hour' => '20:00:00'
                    ]
                );

                Space::firstOrCreate(
                    ['name' => 'Private Office 15A', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $privateOfficeType->id,
                        'capacity' => 6,
                        'price_per_hour' => 150000,
                        'price_per_day' => 1000000,
                        'price_per_month' => 20000000,
                        'open_hour' => '08:00:00',
                        'close_hour' => '20:00:00'
                    ]
                );

                Space::firstOrCreate(
                    ['name' => 'Sky Lounge Coworking', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $coworkingType->id,
                        'capacity' => 30,
                        'price_per_hour' => 80000,
                        'price_per_day' => 560000,
                        'price_per_month' => 12000000,
                        'open_hour' => '07:00:00',
                        'close_hour' => '23:00:00'
                    ]
                );
            }

            // Venue 3: 3 spaces
            if ($venue->id == 5 || $venue->name == 'Green Space Café') {
                Space::firstOrCreate(
                    ['name' => 'Café Meeting Corner', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $meetingRoomType->id,
                        'capacity' => 4,
                        'price_per_hour' => 60000,
                        'price_per_day' => 400000,
                        'price_per_month' => 9000000,
                        'open_hour' => '09:00:00',
                        'close_hour' => '22:00:00'
                    ]
                );

                Space::firstOrCreate(
                    ['name' => 'Garden Coworking Area', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $coworkingType->id,
                        'capacity' => 15,
                        'price_per_hour' => 40000,
                        'price_per_day' => 280000,
                        'price_per_month' => 6000000,
                        'open_hour' => '09:00:00',
                        'close_hour' => '21:00:00'
                    ]
                );

                Space::firstOrCreate(
                    ['name' => 'Private Booth', 'venue_id' => $venue->id],
                    [
                        'space_type_id' => $privateOfficeType->id,
                        'capacity' => 2,
                        'price_per_hour' => 50000,
                        'price_per_day' => 350000,
                        'price_per_month' => 7500000,
                        'open_hour' => '09:00:00',
                        'close_hour' => '22:00:00'
                    ]
                );
            }
        }

        // Attach 4-5 random amenities to each space
        if (!empty($allAmenities)) {
            $spaces = Space::all();
            foreach ($spaces as $space) {
                $randomCount = rand(4, 5);
                $randomAmenities = array_rand(array_flip($allAmenities), min($randomCount, count($allAmenities)));
                if (!is_array($randomAmenities)) {
                    $randomAmenities = [$randomAmenities];
                }
                $space->amenities()->sync($randomAmenities);
            }
            $this->command->info("✅ Attached amenities to all spaces");
        }

        $totalSpaces = Space::count();
        $this->command->info("✅ Created spaces for " . count($venues) . " venues (Total: {$totalSpaces} spaces)");
    }
}
