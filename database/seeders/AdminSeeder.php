<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo role admin đã tồn tại
        $adminRole = Role::firstOrCreate(['role_name' => 'admin']);

        // Tạo admin mặc định
        $admin = User::firstOrCreate(
            ['email' => 'admin@workspace.com'],
            [
                'full_name' => 'System Admin',
                'password_hash' => Hash::make('admin123'),
                'is_active' => true,
                'is_verified' => true,
            ]
        );

        // Gán role admin
        if (!$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id);
        }

        $this->command->info('Admin user created:');
        $this->command->info('Email: admin@workspace.com');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login!');
    }
}
