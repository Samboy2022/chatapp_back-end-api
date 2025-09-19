<?php

/**
 * Quick test to verify fixes
 */

$baseUrl = 'http://127.0.0.1:8000/api';

function makeRequest($method, $url, $data = [], $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ Request successful\n";
        return json_decode($response, true);
    } else {
        echo "❌ Request failed: $response\n";
        return false;
    }
}

echo "🔧 Testing Quick Fixes\n";
echo "======================\n\n";

// Register test user with unique data
$uniqueId = rand(1000, 9999);
$testUser = [
    'name' => 'Quick Test User ' . $uniqueId,
    'email' => 'quicktest' . $uniqueId . '@example.com',
    'phone_number' => '+123456' . $uniqueId,
    'country_code' => '+1',
    'password' => 'testpass123',
    'password_confirmation' => 'testpass123'
];

echo "1. 📝 Testing Registration...\n";
$registerResult = makeRequest('POST', $baseUrl . '/auth/register', $testUser);

if ($registerResult && isset($registerResult['data']['token'])) {
    $token = $registerResult['data']['token'];
    $userId = $registerResult['data']['user']['id'];
    echo "✅ Registration successful (User ID: $userId)\n\n";
    
    // Test contacts
    echo "2. 📞 Testing Contacts API...\n";
    $contactsResult = makeRequest('GET', $baseUrl . '/contacts', [], $token);
    if ($contactsResult) {
        echo "✅ Contacts API working!\n\n";
    }
    
    // Create a second user for P2P messaging
    $secondUser = [
        'name' => 'Second Test User ' . $uniqueId,
        'email' => 'secondtest' . $uniqueId . '@example.com',
        'phone_number' => '+123457' . $uniqueId,
        'country_code' => '+1',
        'password' => 'testpass123',
        'password_confirmation' => 'testpass123'
    ];

    $secondUserResult = makeRequest('POST', $baseUrl . '/auth/register', $secondUser);

    if ($secondUserResult && isset($secondUserResult['data']['user']['id'])) {
        $secondUserId = $secondUserResult['data']['user']['id'];

        // Test P2P message send
        echo "3. 💬 Testing P2P Message Send...\n";
        $messageData = [
            'receiver_id' => $secondUserId,
            'message' => 'Test P2P message',
            'type' => 'text'
        ];
        $messageResult = makeRequest('POST', $baseUrl . '/messages', $messageData, $token);
        if ($messageResult) {
            echo "✅ P2P messaging working!\n\n";
        }
    } else {
        echo "3. 💬 Skipping P2P Message Test (couldn't create second user)\n\n";
    }
    
    // Test status creation
    echo "4. 📱 Testing Status Creation...\n";
    $statusData = [
        'content' => 'Test status update',
        'type' => 'text',
        'privacy' => 'contacts'
    ];
    $statusResult = makeRequest('POST', $baseUrl . '/statuses', $statusData, $token);
    if ($statusResult) {
        echo "✅ Status creation working!\n\n";
    }
    
    // Test group creation
    echo "5. 👥 Testing Group Creation...\n";
    $groupData = [
        'name' => 'Test Group',
        'description' => 'A test group',
        'type' => 'group'
    ];
    $groupResult = makeRequest('POST', $baseUrl . '/groups', $groupData, $token);
    if ($groupResult && isset($groupResult['data']['id'])) {
        $groupId = $groupResult['data']['id'];
        echo "✅ Group creation working! (Group ID: $groupId)\n\n";
        
        // Test adding user to group
        echo "6. 👤 Testing Add User to Group...\n";
        $addUserData = ['user_id' => $secondUserId ?? 1]; // Use second user if available
        $addUserResult = makeRequest('POST', $baseUrl . "/groups/$groupId/users", $addUserData, $token);
        if ($addUserResult) {
            echo "✅ Add user to group working!\n\n";
        }
        
        // Test group message
        echo "7. 📤 Testing Group Message...\n";
        $groupMessageData = [
            'message' => 'Test group message',
            'type' => 'text'
        ];
        $groupMessageResult = makeRequest('POST', $baseUrl . "/groups/$groupId/message", $groupMessageData, $token);
        if ($groupMessageResult) {
            echo "✅ Group messaging working!\n\n";
        }
    }
    
    // Test call initiation
    echo "8. 📞 Testing Call Initiation...\n";
    if (isset($secondUserId)) {
        $callData = [
            'receiver_id' => $secondUserId,
            'type' => 'voice'
        ];
        $callResult = makeRequest('POST', $baseUrl . '/calls', $callData, $token);
        if ($callResult && isset($callResult['data']['id'])) {
            $callId = $callResult['data']['id'];
            echo "✅ Call initiation working! (Call ID: $callId)\n\n";

            // Test call accept with the second user's token (receiver)
            echo "9. ✅ Testing Call Accept...\n";
            $secondUserToken = $secondUserResult['data']['token'] ?? null;
            if ($secondUserToken) {
                $acceptResult = makeRequest('POST', $baseUrl . "/calls/$callId/accept", [], $secondUserToken);
                if ($acceptResult) {
                    echo "✅ Call accept working!\n\n";
                }
            } else {
                echo "⚠️  Skipping call accept test (no second user token)\n\n";
            }
        }
    } else {
        echo "⚠️  Skipping call test (no second user available)\n\n";
    }
    
} else {
    echo "❌ Registration failed, cannot proceed with other tests\n";
}

echo "🎯 Quick Fix Test Complete!\n";
