<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venue;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DemoVenueSeeder extends Seeder
{
    /**
     * Seed demo venues for testing/demo purposes.
     */
    public function run(): void
    {
        // Ensure owner user exists
        $owner = User::firstOrCreate(
            ['email' => 'owner@workspace.com'],
            [
                'full_name' => 'Owner Demo',
                'password_hash' => Hash::make('password'),
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Assign owner role
        $ownerRole = Role::firstOrCreate(['role_name' => 'owner']);
        if (!$owner->roles()->where('role_name', 'owner')->exists()) {
            $owner->roles()->attach($ownerRole->id);
        }

        // Demo venues data
        $venues = [
            [
                'name' => 'Coworking HUST',
                'description' => 'Không gian làm việc chung gần ĐH Bách Khoa',
                'address' => '1 Đại Cồ Việt',
                'street' => 'Đại Cồ Việt',
                'city' => 'Hanoi',
                'latitude' => 21.004,
                'longitude' => 105.843,
                'owner_id' => $owner->id,
                'status' => 'approved'
            ],
            [
                'name' => 'Sky Office Tower',
                'description' => 'Văn phòng cao cấp view thành phố',
                'address' => '72 Trần Đăng Ninh',
                'street' => 'Trần Đăng Ninh',
                'city' => 'Hanoi',
                'latitude' => 21.027,
                'longitude' => 105.795,
                'owner_id' => $owner->id,
                'status' => 'approved'
            ],
            [
                'name' => 'Green Space Café',
                'description' => 'Quán cafe kiêm coworking space yên tĩnh',
                'address' => '45 Nguyễn Trãi',
                'street' => 'Nguyễn Trãi',
                'city' => 'Hanoi',
                'latitude' => 20.998,
                'longitude' => 105.810,
                'owner_id' => $owner->id,
                'status' => 'approved'
            ]
        ];

        foreach ($venues as $venueData) {
            $venue = Venue::firstOrCreate(
                ['name' => $venueData['name'], 'owner_id' => $owner->id],
                $venueData
            );

            // Attach random amenities to each venue (3-5 amenities)
            $amenityIds = \App\Models\Amenity::inRandomOrder()->limit(rand(3, 5))->pluck('id')->toArray();
            $venue->amenities()->syncWithoutDetaching($amenityIds);
        }

        $this->command->info('✅ Created 3 demo venues for owner@workspace.com with amenities');
    }
}
