<?php

/**
 * Quick script to create a test user for the chat app
 * Run this from the Laravel project root: php create_test_user.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Check if test user already exists
    $existingUser = User::where('phone_number', '+1234567890')->first();
    
    if ($existingUser) {
        echo "✅ Test user already exists:\n";
        echo "   Name: {$existingUser->name}\n";
        echo "   Phone: {$existingUser->phone_number}\n";
        echo "   Email: {$existingUser->email}\n";
        echo "   Password: password123\n\n";
        echo "You can use these credentials to log in.\n";
        exit(0);
    }
    
    // Create test user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@farmersnetwork.com',
        'phone_number' => '+1234567890',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'is_online' => false,
        'last_seen' => now(),
    ]);
    
    echo "✅ Test user created successfully!\n\n";
    echo "Login Credentials:\n";
    echo "==================\n";
    echo "Phone: +1234567890\n";
    echo "Password: password123\n\n";
    echo "You can now use these credentials to log in to the mobile app.\n";
    
} catch (Exception $e) {
    echo "❌ Error creating test user: " . $e->getMessage() . "\n";
    echo "\nMake sure you run this from the Laravel project root directory.\n";
    exit(1);
}
