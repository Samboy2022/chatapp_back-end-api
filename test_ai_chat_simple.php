<?php
/**
 * AI Chat API Test Script (Simple)
 * Run: php test_ai_chat_simple.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "AI Chat API Test Script\n";
echo "========================\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// Get/create test user
echo "[1] Setting up test user...\n";
$testUser = \App\Models\User::firstOrCreate(
    ['email' => 'ai_test@example.com'],
    [
        'name' => 'AI Test User',
        'password' => bcrypt('password123'),
        'phone_number' => '+2341234567890',
    ]
);
$token = $testUser->createToken('ai-test-token')->plainTextToken;
echo "    User: {$testUser->name} (ID: {$testUser->id})\n";
echo "    Token created\n\n";

// Helper function
function api($method, $url, $data, $token) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
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
    return ['code' => $code, 'body' => $response, 'json' => json_decode($response, true)];
}

// Test 1: Create new conversation
echo "[2] Create new conversation...\n";
$r = api('POST', "$baseUrl/ai-chat/new", null, $token);
echo "    Status: {$r['code']}\n";
$convId = $r['json']['data']['conversation_id'] ?? \App\Models\AiMessage::generateConversationId();
echo "    Conversation ID: " . substr($convId, 0, 8) . "...\n\n";

// Test 2: Send message (Hausa)
echo "[3] Send message (Hausa test)...\n";
$msg = "Yaya zan shuka masara?";
echo "    Message: $msg\n";
$r = api('POST', "$baseUrl/ai-chat/sync", ['message' => $msg, 'conversation_id' => $convId], $token);
echo "    Status: {$r['code']}\n";
if (isset($r['json']['data']['response'])) {
    echo "    AI Response: " . substr($r['json']['data']['response'], 0, 200) . "...\n\n";
} else {
    echo "    Raw: " . substr($r['body'], 0, 300) . "\n\n";
}

// Test 3: Send follow-up
echo "[4] Send follow-up (English)...\n";
$msg2 = "What about pest control for this?";
echo "    Message: $msg2\n";
$r = api('POST', "$baseUrl/ai-chat/sync", ['message' => $msg2, 'conversation_id' => $convId], $token);
echo "    Status: {$r['code']}\n";
if (isset($r['json']['data']['response'])) {
    echo "    AI Response: " . substr($r['json']['data']['response'], 0, 200) . "...\n\n";
} else {
    echo "    Raw: " . substr($r['body'], 0, 300) . "\n\n";
}

// Test 4: Get conversation list
echo "[5] Get conversation list...\n";
$r = api('GET', "$baseUrl/ai-chat/history?page=1&per_page=10" , null, $token);
echo "    Status: {$r['code']}\n";
if (isset($r['json']['data']['conversations'])) {
    $count = count($r['json']['data']['conversations']);
    echo "    Found: $count conversations\n\n";
} else {
    echo "    Raw: " . substr($r['body'], 0, 200) . "\n\n";
}

// Test 5: Get conversation messages
echo "[6] Get conversation messages...\n";
$r = api('GET', "$baseUrl/ai-chat/history?conversation_id=$convId", null, $token);
echo "    Status: {$r['code']}\n";
if (isset($r['json']['data']['messages'])) {
    $count = count($r['json']['data']['messages']);
    echo "    Found: $count messages\n";
    foreach ($r['json']['data']['messages'] as $m) {
        echo "    - {$m['role']}: " . substr($m['content'], 0, 50) . "...\n";
    }
    echo "\n";
} else {
    echo "    Raw: " . substr($r['body'], 0, 200) . "\n\n";
}

// Test 6: Get last conversation
echo "[7] Get last conversation...\n";
$r = api('GET', "$baseUrl/ai-chat/last", null, $token);
echo "    Status: {$r['code']}\n";
if (isset($r['json']['data']['conversation_id'])) {
    echo "    Last Conv: " . substr($r['json']['data']['conversation_id'], 0, 8) . "...\n\n";
}

echo "========================\n";
echo "ALL TESTS COMPLETE!\n\n";
echo "Token for testing:\n$token\n\n";
echo "Conversation ID:\n$convId\n";
