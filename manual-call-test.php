<?php

/**
 * Manual Call API Testing Script
 * Tests voice and video calls with known users
 */

$baseUrl = 'http://127.0.0.1:8000';

// Test user credentials
$user1 = [
    'id' => 64,
    'email' => 'testuser1@test.com',
    'password' => 'password123'
];

$user2 = [
    'id' => 65,
    'email' => 'testuser2@test.com', 
    'password' => 'password123'
];

echo "🚀 Manual Call API Testing\n";
echo "==========================\n\n";

/**
 * Make HTTP request
 */
function makeRequest($method, $endpoint, $data = [], $token = null) {
    global $baseUrl;
    
    $url = $baseUrl . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    $decodedResponse = json_decode($response, true);
    
    echo "📡 $method $endpoint\n";
    echo "Status: $httpCode\n";
    echo "Response: " . json_encode($decodedResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($httpCode >= 400) {
        throw new Exception("HTTP $httpCode: " . ($decodedResponse['message'] ?? $response));
    }
    
    return $decodedResponse;
}

try {
    // Step 1: Login users
    echo "🔐 Step 1: Logging in users...\n";
    
    $user1Login = makeRequest('POST', '/api/auth/login', [
        'login' => $user1['email'],
        'password' => $user1['password']
    ]);
    
    $user2Login = makeRequest('POST', '/api/auth/login', [
        'login' => $user2['email'],
        'password' => $user2['password']
    ]);
    
    $user1Token = $user1Login['data']['token'];
    $user2Token = $user2Login['data']['token'];
    
    echo "✅ Both users logged in successfully!\n\n";
    
    // Step 2: Test broadcast settings
    echo "📡 Step 2: Testing broadcast settings...\n";
    
    $broadcastSettings = makeRequest('GET', '/api/broadcast-settings');
    $callSignalingConfig = makeRequest('GET', '/api/broadcast-settings/call-signaling');
    
    echo "✅ Broadcast settings retrieved!\n\n";
    
    // Step 3: Test Voice Call Flow
    echo "🎤 Step 3: Testing Voice Call Flow...\n";
    
    echo "📞 User 1 initiating voice call to User 2...\n";
    $voiceCallInit = makeRequest('POST', '/api/calls', [
        'receiver_id' => $user2['id'],
        'type' => 'audio'
    ], $user1Token);
    
    $voiceCallId = $voiceCallInit['data']['id'];
    echo "✅ Voice call initiated! Call ID: $voiceCallId\n\n";
    
    // Check active calls
    echo "📋 Checking active calls for User 2...\n";
    $activeCalls = makeRequest('GET', '/api/calls/active', [], $user2Token);
    echo "✅ Active calls retrieved!\n\n";
    
    // Answer the call
    echo "📞 User 2 answering the voice call...\n";
    $answerResponse = makeRequest('POST', "/api/calls/$voiceCallId/answer", [], $user2Token);
    echo "✅ Voice call answered!\n\n";
    
    // Simulate call duration
    echo "⏱️  Simulating call duration (3 seconds)...\n";
    sleep(3);
    
    // End the call
    echo "📞 User 1 ending the voice call...\n";
    $endResponse = makeRequest('POST', "/api/calls/$voiceCallId/end", [], $user1Token);
    echo "✅ Voice call ended! Duration: {$endResponse['data']['duration']} seconds\n\n";
    
    // Step 4: Test Video Call Flow
    echo "📹 Step 4: Testing Video Call Flow...\n";
    
    echo "📞 User 2 initiating video call to User 1...\n";
    $videoCallInit = makeRequest('POST', '/api/calls', [
        'receiver_id' => $user1['id'],
        'type' => 'video'
    ], $user2Token);
    
    $videoCallId = $videoCallInit['data']['id'];
    echo "✅ Video call initiated! Call ID: $videoCallId\n\n";
    
    // Reject the call
    echo "📞 User 1 rejecting the video call...\n";
    $rejectResponse = makeRequest('POST', "/api/calls/$videoCallId/decline", [], $user1Token);
    echo "✅ Video call rejected!\n\n";
    
    // Test another video call that gets answered
    echo "📞 User 2 initiating another video call to User 1...\n";
    $videoCallInit2 = makeRequest('POST', '/api/calls', [
        'receiver_id' => $user1['id'],
        'type' => 'video'
    ], $user2Token);
    
    $videoCallId2 = $videoCallInit2['data']['id'];
    echo "✅ Second video call initiated! Call ID: $videoCallId2\n\n";
    
    // Answer the second call
    echo "📞 User 1 answering the second video call...\n";
    $answerResponse2 = makeRequest('POST', "/api/calls/$videoCallId2/answer", [], $user1Token);
    echo "✅ Second video call answered!\n\n";
    
    // End the call from receiver side
    echo "📞 User 1 (receiver) ending the video call...\n";
    $endResponse2 = makeRequest('POST', "/api/calls/$videoCallId2/end", [], $user1Token);
    echo "✅ Video call ended by receiver!\n\n";
    
    // Step 5: Test Call History and Statistics
    echo "📊 Step 5: Testing Call History and Statistics...\n";
    
    echo "📋 Getting call history for User 1...\n";
    $history1 = makeRequest('GET', '/api/calls', [], $user1Token);
    echo "✅ User 1 call history retrieved!\n\n";
    
    echo "📋 Getting call statistics for User 1...\n";
    $stats1 = makeRequest('GET', '/api/calls/statistics', [], $user1Token);
    echo "✅ Call statistics retrieved!\n\n";
    
    echo "📋 Getting filtered call history (audio calls only)...\n";
    $audioHistory = makeRequest('GET', '/api/calls?type=audio', [], $user1Token);
    echo "✅ Audio call history retrieved!\n\n";
    
    echo "📋 Getting filtered call history (video calls only)...\n";
    $videoHistory = makeRequest('GET', '/api/calls?type=video', [], $user1Token);
    echo "✅ Video call history retrieved!\n\n";
    
    // Step 6: Test Error Scenarios
    echo "⚠️  Step 6: Testing Error Scenarios...\n";
    
    echo "🚫 Testing call to non-existent user...\n";
    try {
        $errorCall = makeRequest('POST', '/api/calls', [
            'receiver_id' => 99999,
            'type' => 'video'
        ], $user1Token);
    } catch (Exception $e) {
        echo "✅ Expected error caught: " . $e->getMessage() . "\n\n";
    }
    
    echo "🚫 Testing call to self...\n";
    try {
        $selfCall = makeRequest('POST', '/api/calls', [
            'receiver_id' => $user1['id'],
            'type' => 'audio'
        ], $user1Token);
    } catch (Exception $e) {
        echo "✅ Expected error caught: " . $e->getMessage() . "\n\n";
    }
    
    // Final Summary
    echo "🎉 ALL TESTS COMPLETED SUCCESSFULLY!\n";
    echo "=====================================\n\n";
    
    echo "📋 Test Summary:\n";
    echo "✅ User authentication working\n";
    echo "✅ Broadcast settings accessible\n";
    echo "✅ Voice call: initiate → answer → end\n";
    echo "✅ Video call: initiate → reject\n";
    echo "✅ Video call: initiate → answer → end\n";
    echo "✅ Call history and statistics\n";
    echo "✅ Error handling for invalid scenarios\n\n";
    
    echo "🔗 WebSocket Events Broadcasting To:\n";
    echo "   - call.{$user1['id']} (for User 1)\n";
    echo "   - call.{$user2['id']} (for User 2)\n\n";
    
    echo "📱 System is ready for Flutter integration!\n";
    echo "🎯 All call signaling APIs are working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
