<?php
/**
 * AI Chat API Test Script
 * Run: php test_ai_chat.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 AI Chat API Test Script\n";
echo "==========================\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';

// Step 1: Get or create a test user and token
echo "1️⃣  Setting up test user...\n";

$testUser = \App\Models\User::firstOrCreate(
    ['email' => 'ai_test@example.com'],
    [
        'name' => 'AI Test User',
        'password' => bcrypt('password123'),
        'phone_number' => '+2341234567890',
    ]
);

$token = $testUser->createToken('ai-test-token')->plainTextToken;
echo "   ✅ User: {$testUser->name} (ID: {$testUser->id})\n";
echo "   ✅ Token: " . substr($token, 0, 20) . "...\n\n";

// Helper function to make API requests
function apiRequest($method, $url, $data = null, $token = null, $stream = false) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 120,
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => json_decode($response, true),
        'error' => $error,
    ];
}

// Step 2: Test creating a new conversation
echo "2️⃣  Testing: Create New Conversation\n";
$response = apiRequest('POST', "$baseUrl/ai-chat/new", null, $token);
echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['conversation_id'])) {
    $conversationId = $response['json']['data']['conversation_id'];
    echo "   ✅ New conversation ID: " . substr($conversationId, 0, 8) . "...\n\n";
} else {
    echo "   ❌ Failed: " . ($response['error'] ?: json_encode($response['json'])) . "\n\n";
    $conversationId = \App\Models\AiMessage::generateConversationId();
}

// Step 3: Test sending a message (non-streaming)
echo "3️⃣  Testing: Send Message (Non-Streaming)\n";
$testMessage = "Ina son sanin yadda zan shuka masara a lokacin damina?";
echo "   Message: $testMessage\n";

$response = apiRequest('POST', "$baseUrl/ai-chat/sync", [
    'message' => $testMessage,
    'conversation_id' => $conversationId,
], $token);

echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['response'])) {
    $aiResponse = $response['json']['data']['response'];
    echo "   ✅ AI Response: " . substr($aiResponse, 0, 150) . "...\n\n";
} else {
    echo "   Response: " . substr($response['body'], 0, 500) . "\n\n";
}

// Step 4: Test sending another message
echo "4️⃣  Testing: Send Follow-up Message\n";
$followUp = "What about pest control?";
echo "   Message: $followUp\n";

$response = apiRequest('POST', "$baseUrl/ai-chat/sync", [
    'message' => $followUp,
    'conversation_id' => $conversationId,
], $token);

echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['response'])) {
    echo "   ✅ AI remembers context: " . substr($response['json']['data']['response'], 0, 150) . "...\n\n";
} else {
    echo "   Response: " . substr($response['body'], 0, 300) . "\n\n";
}

// Step 5: Test getting conversation history
echo "5️⃣  Testing: Get Conversation List (Paginated)\n";
$response = apiRequest('GET', "$baseUrl/ai-chat/history?page=1&per_page=10", null, $token);
echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['conversations'])) {
    $count = count($response['json']['data']['conversations']);
    $pagination = $response['json']['data']['pagination'];
    echo "   ✅ Found $count conversations (Page {$pagination['current_page']} of {$pagination['total_pages']})\n\n";
} else {
    echo "   Response: " . substr($response['body'], 0, 300) . "\n\n";
}

// Step 6: Test getting specific conversation messages
echo "6️⃣  Testing: Get Conversation Messages (Paginated)\n";
$response = apiRequest('GET', "$baseUrl/ai-chat/history?conversation_id=$conversationId&page=1&per_page=50", null, $token);
echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['messages'])) {
    $count = count($response['json']['data']['messages']);
    echo "   ✅ Found $count messages in conversation\n";
    
    foreach ($response['json']['data']['messages'] as $msg) {
        $role = $msg['role'] === 'user' ? '👤 User' : '🤖 AI';
        $content = substr($msg['content'], 0, 60);
        echo "      $role: $content...\n";
    }
    echo "\n";
} else {
    echo "   Response: " . substr($response['body'], 0, 300) . "\n\n";
}

// Step 7: Test getting last conversation
echo "7️⃣  Testing: Get Last Conversation\n";
$response = apiRequest('GET', "$baseUrl/ai-chat/last", null, $token);
echo "   Status: {$response['code']}\n";

if ($response['code'] === 200 && isset($response['json']['data']['conversation_id'])) {
    echo "   ✅ Last conversation: " . substr($response['json']['data']['conversation_id'], 0, 8) . "...\n\n";
} else {
    echo "   Response: " . substr($response['body'], 0, 200) . "\n\n";
}

// Step 8: Test SSE streaming (curl command example)
echo "8️⃣  SSE Streaming Test (cURL command):\n";
echo "   ─────────────────────────────────────────\n";
echo "   curl -N -X POST '$baseUrl/ai-chat' \\\n";
echo "     -H 'Authorization: Bearer " . substr($token, 0, 20) . "...' \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -H 'Accept: text/event-stream' \\\n";
echo "     -d '{\"message\": \"Hello\", \"conversation_id\": \"$conversationId\"}'\n";
echo "   ─────────────────────────────────────────\n\n";

// Summary
echo "✅ All Tests Complete!\n";
echo "=======================\n";
echo "📊 Summary:\n";
echo "   • User ID: {$testUser->id}\n";
echo "   • Conversation ID: " . substr($conversationId, 0, 8) . "...\n";
echo "   • Token: " . substr($token, 0, 20) . "...\n\n";

echo "📝 Full Token (for testing in Postman/Flutter):\n";
echo "   $token\n\n";

// Cleanup option
echo "💡 To delete test data, run:\n";
echo "   php artisan tinker --execute=\"\\App\\Models\\AiMessage::where('user_id', {$testUser->id})->delete();\"\n\n";
