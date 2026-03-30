<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔧 Seeding Admin Data...\n";
        echo str_repeat("=", 50) . "\n";

        // Admin users to create
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@chatapp.com',
                'phone_number' => '+1999000001',
                'country_code' => '+1',
                'password' => 'admin123',
                'about' => 'System Administrator',
            ],
            [
                'name' => 'ChatWave Admin',
                'email' => 'admin@chatwave.com',
                'phone_number' => '+1999000002',
                'country_code' => '+1',
                'password' => 'password',
                'about' => 'ChatWave Administrator',
            ],
            [
                'name' => 'System Admin',
                'email' => 'superadmin@chatapp.com',
                'phone_number' => '+1999000003',
                'country_code' => '+1',
                'password' => 'admin123',
                'about' => 'Super Administrator',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'phone_number' => $adminData['phone_number'],
                    'country_code' => $adminData['country_code'],
                    'password' => Hash::make($adminData['password']),
                    'about' => $adminData['about'],
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_online' => false,
                    'is_active' => true,
                    'is_blocked' => false,
                    'last_seen_at' => now(),
                ]
            );

            if ($admin->wasRecentlyCreated) {
                echo "✅ Created admin: {$adminData['name']} ({$adminData['email']})\n";
            } else {
                echo "ℹ️  Admin already exists: {$adminData['name']} ({$adminData['email']})\n";
            }
        }

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "🎉 Admin Data Seeded Successfully!\n";
        echo str_repeat("=", 50) . "\n\n";

        echo "📋 Admin Credentials:\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($admins as $adminData) {
            echo "Email: {$adminData['email']}\n";
            echo "Password: {$adminData['password']}\n";
            echo str_repeat("-", 50) . "\n";
        }

        echo "\n🌐 Admin Panel URL:\n";
        echo "http://127.0.0.1:8000/admin/login\n";
        echo "http://localhost:8000/admin/login\n\n";
    }
}
