<?php
/**
 * Forward Message API Test Script
 * Run: php test_forward_message.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Forward Message API Test Script\n";
echo "===============================\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// Helper function
function api($method, $url, $data, $token) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        ],
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $code, 'body' => $response];
}

try {
    // Get/create test users
    echo "[1] Setting up test users...\n";
    $user1 = \App\Models\User::firstOrCreate(
        ['email' => 'forward_test1@example.com'],
        [
            'name' => 'Forward Test User 1',
            'password' => bcrypt('password123'),
            'phone_number' => '+2341234567891',
        ]
    );
    
    $user2 = \App\Models\User::firstOrCreate(
        ['email' => 'forward_test2@example.com'],
        [
            'name' => 'Forward Test User 2',
            'password' => bcrypt('password123'),
            'phone_number' => '+2341234567892',
        ]
    );
    
    $token1 = $user1->createToken('forward-test-token')->plainTextToken;
    echo "    User 1: {$user1->name} (ID: {$user1->id})\n";
    echo "    User 2: {$user2->name} (ID: {$user2->id})\n\n";

    // Create test chats
    echo "[2] Creating test chats...\n";
    $chat1 = \App\Models\Chat::create([
        'name' => 'Test Chat 1',
        'is_group' => false,
        'created_by' => $user1->id,
    ]);
    $chat1->participants()->attach([$user1->id, $user2->id]);
    
    $chat2 = \App\Models\Chat::create([
        'name' => 'Test Chat 2',
        'is_group' => false,
        'created_by' => $user1->id,
    ]);
    $chat2->participants()->attach([$user1->id, $user2->id]);
    
    echo "    Chat 1 ID: {$chat1->id}\n";
    echo "    Chat 2 ID: {$chat2->id}\n\n";

    // Create a test message to forward
    echo "[3] Creating test message...\n";
    $message = \App\Models\Message::create([
        'chat_id' => $chat1->id,
        'sender_id' => $user1->id,
        'message_type' => 'text',
        'content' => 'This is a test message to forward',
        'status' => 'sent',
        'sent_at' => now(),
    ]);
    echo "    Message ID: {$message->id}\n";
    echo "    Content: {$message->content}\n\n";

    // Test forward message API
    echo "[4] Testing forward message API...\n";
    $forwardData = [
        'message_id' => $message->id,
        'target_chat_id' => $chat2->id,
        'additional_text' => 'Forwarded with additional text'
    ];
    
    $response = api('POST', $baseUrl . '/messages/forward', $forwardData, $token1);
    echo "    Response Code: {$response['code']}\n";
    echo "    Response Body: {$response['body']}\n\n";
    
    if ($response['code'] === 200) {
        $responseData = json_decode($response['body'], true);
        if ($responseData['success']) {
            echo "✅ SUCCESS: Message forwarded successfully!\n";
            echo "    Forwarded Message ID: {$responseData['data']['message']['id']}\n";
            echo "    Target Chat ID: {$responseData['data']['message']['chat_id']}\n";
            echo "    Content: {$responseData['data']['message']['content']}\n";
        } else {
            echo "❌ FAILED: API returned success=false\n";
        }
    } else {
        echo "❌ FAILED: HTTP {$response['code']}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";