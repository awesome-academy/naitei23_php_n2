<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

class VenueLocationSeeder extends Seeder
{
    /**
     * Các tọa độ thực tế của các thành phố lớn ở Việt Nam
     */
    private array $cityCoordinates = [
        // Hà Nội
        'hanoi' => [
            ['lat' => 21.0285, 'lng' => 105.8542, 'district' => 'Hoàn Kiếm'],
            ['lat' => 21.0136, 'lng' => 105.8349, 'district' => 'Đống Đa'],
            ['lat' => 21.0227, 'lng' => 105.8019, 'district' => 'Ba Đình'],
            ['lat' => 21.0031, 'lng' => 105.8201, 'district' => 'Thanh Xuân'],
            ['lat' => 20.9980, 'lng' => 105.8439, 'district' => 'Hoàng Mai'],
            ['lat' => 21.0410, 'lng' => 105.7982, 'district' => 'Cầu Giấy'],
            ['lat' => 21.0506, 'lng' => 105.7829, 'district' => 'Nam Từ Liêm'],
        ],
        // Hồ Chí Minh
        'ho chi minh' => [
            ['lat' => 10.7769, 'lng' => 106.7009, 'district' => 'Quận 1'],
            ['lat' => 10.7859, 'lng' => 106.6957, 'district' => 'Quận 3'],
            ['lat' => 10.8031, 'lng' => 106.7144, 'district' => 'Phú Nhuận'],
            ['lat' => 10.7626, 'lng' => 106.6602, 'district' => 'Quận 5'],
            ['lat' => 10.8488, 'lng' => 106.7721, 'district' => 'Thủ Đức'],
            ['lat' => 10.8019, 'lng' => 106.6419, 'district' => 'Tân Bình'],
            ['lat' => 10.7575, 'lng' => 106.6794, 'district' => 'Quận 10'],
        ],
        // Đà Nẵng
        'da nang' => [
            ['lat' => 16.0544, 'lng' => 108.2022, 'district' => 'Hải Châu'],
            ['lat' => 16.0678, 'lng' => 108.2208, 'district' => 'Sơn Trà'],
            ['lat' => 16.0398, 'lng' => 108.2257, 'district' => 'Ngũ Hành Sơn'],
            ['lat' => 16.0718, 'lng' => 108.1520, 'district' => 'Liên Chiểu'],
        ],
        // Hải Phòng
        'hai phong' => [
            ['lat' => 20.8449, 'lng' => 106.6881, 'district' => 'Hồng Bàng'],
            ['lat' => 20.8656, 'lng' => 106.6836, 'district' => 'Ngô Quyền'],
            ['lat' => 20.8278, 'lng' => 106.7251, 'district' => 'Lê Chân'],
        ],
        // Cần Thơ
        'can tho' => [
            ['lat' => 10.0452, 'lng' => 105.7469, 'district' => 'Ninh Kiều'],
            ['lat' => 10.0341, 'lng' => 105.7875, 'district' => 'Bình Thủy'],
        ],
        // Default - random coordinates in Vietnam
        'default' => [
            ['lat' => 21.0285, 'lng' => 105.8542, 'district' => 'Hà Nội'],
            ['lat' => 10.7769, 'lng' => 106.7009, 'district' => 'HCM'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venues = Venue::whereNull('latitude')
            ->orWhereNull('longitude')
            ->orWhere('latitude', 0)
            ->orWhere('longitude', 0)
            ->get();

        if ($venues->isEmpty()) {
            $this->command->info('All venues already have coordinates. Checking if we need sample venues...');
            
            // Nếu không có venue nào, tạo sample venues
            if (Venue::count() === 0) {
                $this->createSampleVenues();
            } else {
                // Cập nhật tất cả venues chưa có tọa độ hợp lệ
                $this->updateAllVenuesWithCoordinates();
            }
            return;
        }

        $this->command->info("Updating coordinates for {$venues->count()} venues...");

        foreach ($venues as $venue) {
            $coordinates = $this->getCoordinatesForCity($venue->city);
            
            // Thêm một chút random offset để các venue không trùng vị trí
            $latOffset = (rand(-100, 100) / 10000);
            $lngOffset = (rand(-100, 100) / 10000);

            $venue->update([
                'latitude' => $coordinates['lat'] + $latOffset,
                'longitude' => $coordinates['lng'] + $lngOffset,
            ]);

            $this->command->info("Updated: {$venue->name} - Lat: {$venue->latitude}, Lng: {$venue->longitude}");
        }

        $this->command->info('Venue coordinates updated successfully!');
    }

    /**
     * Lấy tọa độ dựa trên tên thành phố
     */
    private function getCoordinatesForCity(?string $city): array
    {
        if (!$city) {
            return $this->getRandomCoordinate('default');
        }

        $cityLower = strtolower($city);
        
        // Mapping các tên thành phố phổ biến
        $cityMappings = [
            'hanoi' => 'hanoi',
            'hà nội' => 'hanoi',
            'ha noi' => 'hanoi',
            'ho chi minh' => 'ho chi minh',
            'hồ chí minh' => 'ho chi minh',
            'hcm' => 'ho chi minh',
            'saigon' => 'ho chi minh',
            'sài gòn' => 'ho chi minh',
            'da nang' => 'da nang',
            'đà nẵng' => 'da nang',
            'danang' => 'da nang',
            'hai phong' => 'hai phong',
            'hải phòng' => 'hai phong',
            'haiphong' => 'hai phong',
            'can tho' => 'can tho',
            'cần thơ' => 'can tho',
            'cantho' => 'can tho',
        ];

        foreach ($cityMappings as $key => $mappedCity) {
            if (str_contains($cityLower, $key)) {
                return $this->getRandomCoordinate($mappedCity);
            }
        }

        return $this->getRandomCoordinate('default');
    }

    /**
     * Lấy ngẫu nhiên một tọa độ trong thành phố
     */
    private function getRandomCoordinate(string $city): array
    {
        $coordinates = $this->cityCoordinates[$city] ?? $this->cityCoordinates['default'];
        return $coordinates[array_rand($coordinates)];
    }

    /**
     * Cập nhật tất cả venues với tọa độ
     */
    private function updateAllVenuesWithCoordinates(): void
    {
        $venues = Venue::all();
        
        foreach ($venues as $venue) {
            if ($venue->latitude && $venue->longitude && $venue->latitude != 0 && $venue->longitude != 0) {
                continue;
            }

            $coordinates = $this->getCoordinatesForCity($venue->city);
            
            $latOffset = (rand(-100, 100) / 10000);
            $lngOffset = (rand(-100, 100) / 10000);

            $venue->update([
                'latitude' => $coordinates['lat'] + $latOffset,
                'longitude' => $coordinates['lng'] + $lngOffset,
            ]);

            $this->command->info("Updated: {$venue->name}");
        }
    }

    /**
     * Tạo sample venues nếu database trống
     */
    private function createSampleVenues(): void
    {
        $this->command->info('Creating sample venues...');

        $sampleVenues = [
            // Hà Nội
            [
                'name' => 'Hanoi Coworking Hub',
                'address' => '15 Trần Hưng Đạo, Hoàn Kiếm',
                'city' => 'Hanoi',
                'description' => 'Modern coworking space in the heart of Hanoi with stunning city views.',
                'latitude' => 21.0285,
                'longitude' => 105.8542,
            ],
            [
                'name' => 'Tech Valley Office',
                'address' => '88 Cầu Giấy, Cầu Giấy',
                'city' => 'Hanoi',
                'description' => 'Premium office space for tech startups and enterprises.',
                'latitude' => 21.0410,
                'longitude' => 105.7982,
            ],
            [
                'name' => 'West Lake Business Center',
                'address' => '25 Quảng An, Tây Hồ',
                'city' => 'Hanoi',
                'description' => 'Lakeside business center with meeting rooms and event spaces.',
                'latitude' => 21.0650,
                'longitude' => 105.8270,
            ],
            // Hồ Chí Minh
            [
                'name' => 'Saigon Innovation Space',
                'address' => '123 Nguyễn Huệ, Quận 1',
                'city' => 'Ho Chi Minh',
                'description' => 'Creative workspace in District 1 with flexible options.',
                'latitude' => 10.7769,
                'longitude' => 106.7009,
            ],
            [
                'name' => 'Thu Duc Tech Park',
                'address' => '456 Võ Văn Ngân, Thủ Đức',
                'city' => 'Ho Chi Minh',
                'description' => 'Large scale tech park with multiple office configurations.',
                'latitude' => 10.8488,
                'longitude' => 106.7721,
            ],
            [
                'name' => 'District 3 Creative Hub',
                'address' => '78 Võ Văn Tần, Quận 3',
                'city' => 'Ho Chi Minh',
                'description' => 'Artistic workspace for designers and creative professionals.',
                'latitude' => 10.7859,
                'longitude' => 106.6957,
            ],
            // Đà Nẵng
            [
                'name' => 'Da Nang Beach Office',
                'address' => '99 Võ Nguyên Giáp, Sơn Trà',
                'city' => 'Da Nang',
                'description' => 'Beachfront office space with ocean views.',
                'latitude' => 16.0678,
                'longitude' => 108.2208,
            ],
            [
                'name' => 'Dragon Bridge Workspace',
                'address' => '45 Bạch Đằng, Hải Châu',
                'city' => 'Da Nang',
                'description' => 'Central location workspace near iconic Dragon Bridge.',
                'latitude' => 16.0544,
                'longitude' => 108.2022,
            ],
            // Hải Phòng
            [
                'name' => 'Hai Phong Business Hub',
                'address' => '12 Lạch Tray, Ngô Quyền',
                'city' => 'Hai Phong',
                'description' => 'Professional business center in Hai Phong.',
                'latitude' => 20.8656,
                'longitude' => 106.6836,
            ],
            // Cần Thơ
            [
                'name' => 'Mekong Coworking',
                'address' => '30 Hòa Bình, Ninh Kiều',
                'city' => 'Can Tho',
                'description' => 'Coworking space in the heart of Mekong Delta.',
                'latitude' => 10.0452,
                'longitude' => 105.7469,
            ],
        ];

        // Cần có owner_id, kiểm tra xem có user nào không
        $owner = DB::table('users')->first();
        
        if (!$owner) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        foreach ($sampleVenues as $venueData) {
            $venue = Venue::create(array_merge($venueData, [
                'owner_id' => $owner->id,
                'status' => 'approved', // pending, approved, blocked
            ]));
            
            $this->command->info("Created: {$venue->name}");
        }

        $this->command->info('Sample venues created successfully!');
    }
}
