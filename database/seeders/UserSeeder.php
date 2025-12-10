<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo role user đã tồn tại
        $userRole = Role::firstOrCreate(['role_name' => 'user']);

        // Tạo regular user mặc định
        $user = User::firstOrCreate(
            ['email' => 'user@workspace.com'],
            [
                'full_name' => 'Demo User',
                'password_hash' => Hash::make('password'),
                'is_active' => true,
                'is_verified' => true, // Bypass email verification for demo
            ]
        );

        // Gán role user
        if (!$user->roles()->where('role_name', 'user')->exists()) {
            $user->roles()->attach($userRole->id);
        }

        $this->command->info('Demo user created:');
        $this->command->info('Email: user@workspace.com');
        $this->command->info('Password: password');
    }
}
