<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create manager users
        $managers = [
            [
                'full_name' => 'Manager One',
                'email' => 'manager1@workspace.com',
                'password_hash' => Hash::make('password'),
                'phone_number' => '0901234561',
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'full_name' => 'Manager Two',
                'email' => 'manager2@workspace.com',
                'password_hash' => Hash::make('password'),
                'phone_number' => '0901234562',
                'is_active' => true,
                'is_verified' => true,
            ],
        ];

        $createdManagers = [];
        foreach ($managers as $managerData) {
            $manager = User::firstOrCreate(
                ['email' => $managerData['email']],
                $managerData
            );
            $createdManagers[] = $manager;
        }

        // Assign managers to venues (each venue gets 1-2 managers)
        $venues = Venue::all();

        if ($venues->count() > 0 && count($createdManagers) > 0) {
            foreach ($venues as $index => $venue) {
                // First venue: both managers
                if ($index == 0) {
                    $venue->managers()->syncWithoutDetaching([
                        $createdManagers[0]->id,
                        $createdManagers[1]->id
                    ]);
                }
                // Other venues: one manager each
                else {
                    $managerIndex = $index % count($createdManagers);
                    $venue->managers()->syncWithoutDetaching($createdManagers[$managerIndex]->id);
                }
            }

            $this->command->info('✅ Created 2 managers and assigned to venues');
            $this->command->info('   - manager1@workspace.com / password');
            $this->command->info('   - manager2@workspace.com / password');
        }
    }
}
