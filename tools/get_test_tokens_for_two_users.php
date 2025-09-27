<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Ensure we have at least two users to test with
if (User::count() < 2) {
    // If not, create a second user for testing purposes
    if (method_exists(User::class, 'factory')) {
        User::factory()->create([
            'email' => 'testuser2@example.com',
        ]);
    } else {
        User::create([
            'name' => 'Test User 2',
            'email' => 'testuser2@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}

$users = User::take(2)->get();

if ($users->count() < 2) {
    echo json_encode(['success' => false, 'message' => 'Could not find or create two users to test with.']);
    exit(1);
}

$userA = $users[0];
$userB = $users[1];

// Delete old tokens to ensure we get new ones
$userA->tokens()->delete();
$userB->tokens()->delete();

$tokenA = $userA->createToken('user-a-token')->plainTextToken;
$tokenB = $userB->createToken('user-b-token')->plainTextToken;

echo json_encode([
    'user_a' => ['id' => $userA->id, 'name' => $userA->name, 'token' => $tokenA],
    'user_b' => ['id' => $userB->id, 'name' => $userB->name, 'token' => $tokenB],
], JSON_PRETTY_PRINT);
