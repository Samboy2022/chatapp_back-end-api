<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Checking existing users in database...\n";
echo "=====================================\n";

try {
    $userCount = User::count();
    echo "Total users in database: {$userCount}\n\n";
    
    if ($userCount > 0) {
        echo "First 5 users:\n";
        $users = User::take(5)->get(['id', 'name', 'email', 'phone_number']);
        foreach ($users as $user) {
            echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Phone: {$user->phone_number}\n";
        }
    } else {
        echo "No users found. Creating a test user...\n";
        
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+1234567890',
            'country_code' => '+1',
            'password' => bcrypt('password'),
        ]);
        
        echo "Test user created: ID {$testUser->id}, Email: {$testUser->email}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
