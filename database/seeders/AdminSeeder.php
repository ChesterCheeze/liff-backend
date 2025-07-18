<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user configuration
        $adminData = [
            'name' => env('ADMIN_NAME', 'System Administrator'),
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
            'role' => 'admin',
            'email_verified_at' => now(),
        ];

        // Check if admin user already exists
        $existingAdmin = User::where('email', $adminData['email'])
            ->orWhere('role', 'admin')
            ->first();

        if ($existingAdmin) {
            // Update existing admin user
            $existingAdmin->update([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => $adminData['password'],
                'role' => 'admin',
                'email_verified_at' => $adminData['email_verified_at'],
            ]);

            $this->command->info('Admin user updated: '.$adminData['email']);
        } else {
            // Create new admin user
            User::create($adminData);
            $this->command->info('Admin user created: '.$adminData['email']);
        }

        // Create additional admin users if specified in environment
        if (env('SEED_MULTIPLE_ADMINS', false)) {
            $additionalAdmins = [
                [
                    'name' => 'Admin User 2',
                    'email' => 'admin2@example.com',
                    'password' => Hash::make('admin123'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ],
                [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@example.com',
                    'password' => Hash::make('superadmin123'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ],
            ];

            foreach ($additionalAdmins as $admin) {
                User::updateOrCreate(
                    ['email' => $admin['email']],
                    $admin
                );
                $this->command->info('Additional admin user created/updated: '.$admin['email']);
            }
        }
    }
}
