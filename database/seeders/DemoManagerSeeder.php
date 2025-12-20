<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Venue;
use Illuminate\Support\Facades\Hash;

class DemoManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a demo manager account for quick login testing.
     * Assigns manager to at least 1 venue + some spaces for demo.
     * Idempotent: safe to run multiple times.
     */
    public function run(): void
    {
        // Ensure manager role exists
        $managerRole = Role::firstOrCreate(['role_name' => 'manager']);

        // Create or update demo manager account
        $manager = User::firstOrCreate(
            ['email' => 'manager@workspace.com'],
            [
                'full_name' => 'Demo Manager',
                'password_hash' => Hash::make('password'),
                'phone_number' => '0901234560',
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Assign manager role if not already assigned
        if (!$manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }

        // Assign manager to first approved venue (if exists)
        $venue = Venue::where('status', 'approved')->first();
        if ($venue) {
            $venue->managers()->syncWithoutDetaching($manager->id);
            $this->command->info('✅ Manager assigned to venue: ' . $venue->name);
        }

        $this->command->info('✅ Demo manager account created/updated');
        $this->command->info('   Email: manager@workspace.com');
        $this->command->info('   Password: password');
    }
}
