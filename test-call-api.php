<?php

/**
 * Call API Testing Script
 * 
 * This script tests voice and video call functionality through the API
 * Run with: php test-call-api.php
 */

require_once 'vendor/autoload.php';

class CallAPITester
{
    private $baseUrl = 'http://127.0.0.1:8000';
    private $user1Token = null;
    private $user2Token = null;
    private $user1Id = null;
    private $user2Id = null;
    
    public function __construct()
    {
        echo "🚀 Starting Call API Testing...\n\n";
    }
    
    /**
     * Run all tests
     */
    public function runTests()
    {
        try {
            // Step 1: Setup test users and get tokens
            $this->setupTestUsers();
            
            // Step 2: Test broadcast settings
            $this->testBroadcastSettings();
            
            // Step 3: Test voice call flow
            $this->testVoiceCallFlow();
            
            // Step 4: Test video call flow
            $this->testVideoCallFlow();
            
            // Step 5: Test call history and statistics
            $this->testCallHistoryAndStats();
            
            // Step 6: Test admin endpoints
            $this->testAdminEndpoints();
            
            echo "\n✅ All tests completed successfully!\n";
            
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    
    /**
     * Setup test users and get authentication tokens
     */
    private function setupTestUsers()
    {
        echo "📋 Setting up test users...\n";
        
        // Create or get test users
        $this->createTestUser('testuser1@example.com', 'Test User 1', 'password123', '+1234567890');
        $this->createTestUser('testuser2@example.com', 'Test User 2', 'password123', '+1234567891');

        // Login and get tokens
        $this->user1Token = $this->loginUser('testuser1@example.com', 'password123');
        $this->user2Token = $this->loginUser('testuser2@example.com', 'password123');

        // Get user IDs
        $user1Data = $this->makeRequest('GET', '/api/auth/user', [], $this->user1Token);
        $user2Data = $this->makeRequest('GET', '/api/auth/user', [], $this->user2Token);
        
        $this->user1Id = $user1Data['id'];
        $this->user2Id = $user2Data['id'];
        
        echo "✅ Test users setup complete\n";
        echo "   User 1: ID={$this->user1Id}, Email=testuser1@example.com\n";
        echo "   User 2: ID={$this->user2Id}, Email=testuser2@example.com\n\n";
    }
    
    /**
     * Test broadcast settings endpoints
     */
    private function testBroadcastSettings()
    {
        echo "📡 Testing broadcast settings...\n";
        
        // Test main broadcast settings
        $settings = $this->makeRequest('GET', '/api/broadcast-settings');
        $this->assertArrayHasKey('success', $settings);
        $this->assertArrayHasKey('data', $settings);
        $this->assertArrayHasKey('call_signaling', $settings['data']);
        echo "✅ Broadcast settings endpoint working\n";
        
        // Test call signaling specific config
        $callConfig = $this->makeRequest('GET', '/api/broadcast-settings/call-signaling');
        $this->assertArrayHasKey('success', $callConfig);
        $this->assertArrayHasKey('call_events', $callConfig['data']);
        echo "✅ Call signaling config endpoint working\n\n";
    }
    
    /**
     * Test complete voice call flow
     */
    private function testVoiceCallFlow()
    {
        echo "🎤 Testing Voice Call Flow...\n";
        
        // 1. Initiate voice call
        echo "   📞 User 1 initiating voice call to User 2...\n";
        $callData = $this->makeRequest('POST', '/api/calls', [
            'receiver_id' => $this->user2Id,
            'type' => 'audio'
        ], $this->user1Token);
        
        $this->assertArrayHasKey('success', $callData);
        $this->assertTrue($callData['success']);
        $this->assertArrayHasKey('data', $callData);
        
        $callId = $callData['data']['id'];
        $this->assertEquals('audio', $callData['data']['call_type']);
        $this->assertEquals('ringing', $callData['data']['status']);
        echo "   ✅ Voice call initiated successfully (Call ID: $callId)\n";
        
        // 2. Check active calls
        $activeCalls = $this->makeRequest('GET', '/api/calls/active', [], $this->user2Token);
        $this->assertArrayHasKey('success', $activeCalls);
        $this->assertGreaterThan(0, count($activeCalls['data']));
        echo "   ✅ Active call visible to receiver\n";
        
        // 3. Answer the call
        echo "   📞 User 2 answering the call...\n";
        $answerResponse = $this->makeRequest('POST', "/api/calls/$callId/answer", [], $this->user2Token);
        $this->assertArrayHasKey('success', $answerResponse);
        $this->assertTrue($answerResponse['success']);
        echo "   ✅ Call answered successfully\n";
        
        // 4. Simulate call duration
        echo "   ⏱️  Simulating call duration (2 seconds)...\n";
        sleep(2);
        
        // 5. End the call
        echo "   📞 User 1 ending the call...\n";
        $endResponse = $this->makeRequest('POST', "/api/calls/$callId/end", [], $this->user1Token);
        $this->assertArrayHasKey('success', $endResponse);
        $this->assertTrue($endResponse['success']);
        $this->assertEquals('ended', $endResponse['data']['status']);
        $this->assertGreaterThan(0, $endResponse['data']['duration']);
        echo "   ✅ Call ended successfully (Duration: {$endResponse['data']['duration']} seconds)\n\n";
    }
    
    /**
     * Test complete video call flow
     */
    private function testVideoCallFlow()
    {
        echo "📹 Testing Video Call Flow...\n";
        
        // 1. Initiate video call
        echo "   📞 User 2 initiating video call to User 1...\n";
        $callData = $this->makeRequest('POST', '/api/calls', [
            'receiver_id' => $this->user1Id,
            'type' => 'video'
        ], $this->user2Token);
        
        $this->assertArrayHasKey('success', $callData);
        $this->assertTrue($callData['success']);
        
        $callId = $callData['data']['id'];
        $this->assertEquals('video', $callData['data']['call_type']);
        $this->assertEquals('ringing', $callData['data']['status']);
        echo "   ✅ Video call initiated successfully (Call ID: $callId)\n";
        
        // 2. Reject the call
        echo "   📞 User 1 rejecting the call...\n";
        $rejectResponse = $this->makeRequest('POST', "/api/calls/$callId/decline", [], $this->user1Token);
        $this->assertArrayHasKey('success', $rejectResponse);
        $this->assertTrue($rejectResponse['success']);
        $this->assertEquals('declined', $rejectResponse['data']['status']);
        echo "   ✅ Call rejected successfully\n";
        
        // 3. Test another video call that gets answered
        echo "   📞 User 2 initiating another video call to User 1...\n";
        $callData2 = $this->makeRequest('POST', '/api/calls', [
            'receiver_id' => $this->user1Id,
            'type' => 'video'
        ], $this->user2Token);
        
        $callId2 = $callData2['data']['id'];
        echo "   ✅ Second video call initiated (Call ID: $callId2)\n";
        
        // 4. Answer the second call
        echo "   📞 User 1 answering the second call...\n";
        $answerResponse = $this->makeRequest('POST', "/api/calls/$callId2/answer", [], $this->user1Token);
        $this->assertTrue($answerResponse['success']);
        echo "   ✅ Second call answered successfully\n";
        
        // 5. End the call from receiver side
        echo "   📞 User 1 (receiver) ending the call...\n";
        $endResponse = $this->makeRequest('POST', "/api/calls/$callId2/end", [], $this->user1Token);
        $this->assertTrue($endResponse['success']);
        echo "   ✅ Video call ended successfully\n\n";
    }
    
    /**
     * Test call history and statistics
     */
    private function testCallHistoryAndStats()
    {
        echo "📊 Testing Call History and Statistics...\n";
        
        // 1. Get call history for user 1
        $history1 = $this->makeRequest('GET', '/api/calls', [], $this->user1Token);
        $this->assertArrayHasKey('success', $history1);
        $this->assertArrayHasKey('data', $history1);
        echo "   ✅ User 1 call history retrieved\n";
        
        // 2. Get call history for user 2
        $history2 = $this->makeRequest('GET', '/api/calls', [], $this->user2Token);
        $this->assertArrayHasKey('success', $history2);
        echo "   ✅ User 2 call history retrieved\n";
        
        // 3. Get call statistics
        $stats1 = $this->makeRequest('GET', '/api/calls/statistics', [], $this->user1Token);
        $this->assertArrayHasKey('success', $stats1);
        $this->assertArrayHasKey('total_calls', $stats1['data']);
        $this->assertArrayHasKey('video_calls', $stats1['data']);
        $this->assertArrayHasKey('audio_calls', $stats1['data']);
        echo "   ✅ Call statistics retrieved\n";
        
        // 4. Test filtered call history
        $audioHistory = $this->makeRequest('GET', '/api/calls?type=audio', [], $this->user1Token);
        $this->assertArrayHasKey('success', $audioHistory);
        echo "   ✅ Filtered call history (audio) retrieved\n";
        
        $videoHistory = $this->makeRequest('GET', '/api/calls?type=video', [], $this->user1Token);
        $this->assertArrayHasKey('success', $videoHistory);
        echo "   ✅ Filtered call history (video) retrieved\n\n";
    }
    
    /**
     * Test admin endpoints (if accessible)
     */
    private function testAdminEndpoints()
    {
        echo "👑 Testing Admin Endpoints...\n";
        
        try {
            // Note: These might require admin authentication
            $activeCallsAdmin = $this->makeRequest('GET', '/admin/calls/active', [], $this->user1Token);
            if (isset($activeCallsAdmin['success'])) {
                echo "   ✅ Admin active calls endpoint accessible\n";
            }
        } catch (Exception $e) {
            echo "   ⚠️  Admin endpoints require admin authentication (expected)\n";
        }
        
        echo "\n";
    }
    
    /**
     * Create a test user
     */
    private function createTestUser($email, $name, $password, $phone = '+1234567890')
    {
        try {
            $this->makeRequest('POST', '/api/auth/register', [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'phone_number' => $phone,
                'country_code' => '+1'
            ]);
        } catch (Exception $e) {
            // User might already exist, that's okay
        }
    }

    /**
     * Login user and get token
     */
    private function loginUser($email, $password)
    {
        $response = $this->makeRequest('POST', '/api/auth/login', [
            'login' => $email,
            'password' => $password
        ]);

        if (!isset($response['data']['token'])) {
            throw new Exception("Failed to login user: $email");
        }

        return $response['data']['token'];
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($method, $endpoint, $data = [], $token = null)
    {
        $url = $this->baseUrl . $endpoint;
        
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
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP $httpCode: " . ($decodedResponse['message'] ?? $response));
        }
        
        return $decodedResponse;
    }
    
    /**
     * Assert array has key
     */
    private function assertArrayHasKey($key, $array)
    {
        if (!array_key_exists($key, $array)) {
            throw new Exception("Array does not have key: $key");
        }
    }
    
    /**
     * Assert true
     */
    private function assertTrue($value)
    {
        if (!$value) {
            throw new Exception("Expected true, got false");
        }
    }
    
    /**
     * Assert equals
     */
    private function assertEquals($expected, $actual)
    {
        if ($expected !== $actual) {
            throw new Exception("Expected '$expected', got '$actual'");
        }
    }
    
    /**
     * Assert greater than
     */
    private function assertGreaterThan($expected, $actual)
    {
        if ($actual <= $expected) {
            throw new Exception("Expected value greater than $expected, got $actual");
        }
    }
}

// Run the tests
$tester = new CallAPITester();
$tester->runTests();
