<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed all the application data
        $this->call([
            AdminSeeder::class,
            SettingsSeeder::class,
            BroadcastSettingsSeeder::class,
            SampleDataSeeder::class,
        ]);

        echo "\n🎉 ChatWave Database Seeded Successfully!\n";
        echo "==============================================\n";
        echo "Admin Login: http://127.0.0.1:8000/admin/login\n";
        echo "Primary Admin: admin@chatapp.com (admin123)\n";
        echo "Secondary Admin: admin@chatwave.com (password)\n";
        echo "==============================================\n\n";
    }
}
