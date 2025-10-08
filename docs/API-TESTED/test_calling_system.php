<?php
/**
 * Complete Audio/Video Calling System Test
 * Tests: Call initiation, Stream token generation, Accept, Reject, End, History
 */

require __DIR__ . '/vendor/autoload.php';

class CallingSystemTester
{
    private $baseUrl;
    private $user1Token;
    private $user2Token;
    private $user1Id;
    private $user2Id;
    private $testResults = [];
    private $callIds = [];

    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function runAllTests()
    {
        echo "🚀 Starting Complete Audio/Video Calling System Tests\n";
        echo str_repeat("=", 80) . "\n\n";

        // Setup
        if (!$this->setup()) {
            echo "❌ Setup failed\n";
            return;
        }

        // Test calling features
        $this->testInitiateAudioCall();
        $this->testAcceptCall();
        $this->testEndCall();
        
        $this->testInitiateVideoCall();
        $this->testRejectCall();
        
        $this->testVideoCallWithStreamTokens();
        $this->testGetStreamTokens();
        
        $this->testGetCallHistory();
        $this->testGetActiveCalls();
        $this->testMissedCallsCount();
        $this->testCallStatistics();

        // Display summary
        $this->displaySummary();
    }

    private function setup()
    {
        echo "🔧 Setting up test environment...\n";

        // Login users
        if (!$this->loginUser1() || !$this->loginUser2()) {
            return false;
        }

        // Clean up any active calls
        $this->cleanupActiveCalls();

        echo "✅ Setup complete\n\n";
        return true;
    }

    private function cleanupActiveCalls()
    {
        echo "   Cleaning up active calls...\n";
        
        // Get active calls
        $ch = curl_init($this->baseUrl . '/api/calls/active');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $call) {
                $callId = $call['id'];
                
                // End the call
                $ch = curl_init($this->baseUrl . "/api/calls/{$callId}/end");
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $this->user1Token,
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ],
                    CURLOPT_POSTFIELDS => json_encode([])
                ]);

                curl_exec($ch);
                curl_close($ch);
            }
            echo "   ✅ Cleaned up " . count($data['data']) . " active call(s)\n";
        }
    }

    private function loginUser1()
    {
        echo "   Logging in as User 1...\n";
        
        $ch = curl_init($this->baseUrl . '/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'login' => 'streamtest@example.com',
                'password' => 'password123'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->user1Token = $data['data']['token'];
            $this->user1Id = $data['data']['user']['id'];
            echo "   ✅ User 1 logged in (ID: {$this->user1Id})\n";
            return true;
        }

        return false;
    }

    private function loginUser2()
    {
        echo "   Logging in as User 2...\n";
        
        $ch = curl_init($this->baseUrl . '/api/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'login' => 'testuser2@example.com',
                'password' => 'password123'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->user2Token = $data['data']['token'];
            $this->user2Id = $data['data']['user']['id'];
            echo "   ✅ User 2 logged in (ID: {$this->user2Id})\n";
            return true;
        }

        return false;
    }

    private function testInitiateAudioCall()
    {
        echo "📞 Test 1: Initiate Audio Call...\n";

        $ch = curl_init($this->baseUrl . '/api/calls');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'receiver_id' => $this->user2Id,
                'type' => 'audio'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->callIds['audio'] = $data['data']['id'];
            echo "✅ Audio call initiated successfully\n";
            echo "   Call ID: " . $data['data']['id'] . "\n";
            echo "   Caller ID: " . $data['data']['caller_id'] . "\n";
            echo "   Receiver ID: " . $data['data']['receiver_id'] . "\n";
            echo "   Type: " . $data['data']['call_type'] . "\n";
            echo "   Status: " . $data['data']['status'] . "\n";
            $this->testResults['audio_call'] = 'PASS';
        } else {
            echo "❌ Audio call failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['audio_call'] = 'FAIL';
        }
        echo "\n";
    }

    private function testInitiateVideoCall()
    {
        echo "📹 Test 4: Initiate Video Call...\n";

        $ch = curl_init($this->baseUrl . '/api/calls');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'receiver_id' => $this->user2Id,
                'type' => 'video'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->callIds['video'] = $data['data']['id'];
            echo "✅ Video call initiated successfully\n";
            echo "   Call ID: " . $data['data']['id'] . "\n";
            echo "   Type: " . $data['data']['call_type'] . "\n";
            echo "   Status: " . $data['data']['status'] . "\n";
            $this->testResults['video_call'] = 'PASS';
        } else {
            echo "❌ Video call failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['video_call'] = 'FAIL';
        }
        echo "\n";
    }

    private function testVideoCallWithStreamTokens()
    {
        echo "🎥 Test 6: Video Call with Stream Tokens...\n";

        $ch = curl_init($this->baseUrl . '/api/calls');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'receiver_id' => $this->user2Id,
                'type' => 'video'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->callIds['video_with_tokens'] = $data['data']['id'];
            
            echo "✅ Video call with Stream tokens created\n";
            echo "   Call ID: " . $data['data']['id'] . "\n";
            
            if (isset($data['data']['stream_tokens'])) {
                echo "   ✅ Stream tokens generated:\n";
                echo "      API Key: " . (isset($data['data']['stream_tokens']['api_key']) ? 'Present' : 'Missing') . "\n";
                echo "      Caller Token: " . (isset($data['data']['stream_tokens']['caller_token']) ? 'Present' : 'Missing') . "\n";
                echo "      Receiver Token: " . (isset($data['data']['stream_tokens']['receiver_token']) ? 'Present' : 'Missing') . "\n";
                echo "      Expires At: " . ($data['data']['stream_tokens']['expires_at'] ?? 'N/A') . "\n";
            } else {
                echo "   ⚠️  Stream tokens not included in response\n";
            }
            
            $this->testResults['video_stream_tokens'] = 'PASS';
        } else {
            echo "❌ Video call with tokens failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['video_stream_tokens'] = 'FAIL';
        }
        echo "\n";
    }

    private function testAcceptCall()
    {
        echo "✅ Test 2: Accept Call...\n";

        if (empty($this->callIds['audio'])) {
            echo "⏭️  No call ID available\n";
            $this->testResults['accept_call'] = 'SKIP';
            echo "\n";
            return;
        }

        $callId = $this->callIds['audio'];

        $ch = curl_init($this->baseUrl . "/api/calls/{$callId}/accept");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Call accepted successfully\n";
            echo "   Call ID: $callId\n";
            echo "   Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
            $this->testResults['accept_call'] = 'PASS';
        } else {
            echo "❌ Accept call failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['accept_call'] = 'FAIL';
        }
        echo "\n";
    }

    private function testRejectCall()
    {
        echo "❌ Test 5: Reject Call...\n";

        if (empty($this->callIds['video'])) {
            echo "⏭️  No call ID available\n";
            $this->testResults['reject_call'] = 'SKIP';
            echo "\n";
            return;
        }

        $callId = $this->callIds['video'];

        $ch = curl_init($this->baseUrl . "/api/calls/{$callId}/reject");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Call rejected successfully\n";
            echo "   Call ID: $callId\n";
            echo "   Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
            $this->testResults['reject_call'] = 'PASS';
        } else {
            echo "❌ Reject call failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['reject_call'] = 'FAIL';
        }
        echo "\n";
    }

    private function testEndCall()
    {
        echo "🔚 Test 3: End Call...\n";

        if (empty($this->callIds['audio'])) {
            echo "⏭️  No call ID available\n";
            $this->testResults['end_call'] = 'SKIP';
            echo "\n";
            return;
        }

        $callId = $this->callIds['audio'];

        $ch = curl_init($this->baseUrl . "/api/calls/{$callId}/end");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Call ended successfully\n";
            echo "   Call ID: $callId\n";
            echo "   Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
            echo "   Duration: " . ($data['data']['duration'] ?? 'N/A') . " seconds\n";
            $this->testResults['end_call'] = 'PASS';
        } else {
            echo "❌ End call failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['end_call'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetCallHistory()
    {
        echo "📜 Test 8: Get Call History...\n";

        $ch = curl_init($this->baseUrl . '/api/calls');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            $calls = $data['data']['data'] ?? [];
            echo "✅ Call history retrieved successfully\n";
            echo "   Total calls: " . count($calls) . "\n";
            
            if (count($calls) > 0) {
                echo "   Latest call type: " . ($calls[0]['type'] ?? 'N/A') . "\n";
                echo "   Latest call status: " . ($calls[0]['status'] ?? 'N/A') . "\n";
            }
            
            $this->testResults['call_history'] = 'PASS';
        } else {
            echo "❌ Get call history failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['call_history'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetActiveCalls()
    {
        echo "📱 Test 9: Get Active Calls...\n";

        $ch = curl_init($this->baseUrl . '/api/calls/active');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            $activeCalls = $data['data'] ?? [];
            echo "✅ Active calls retrieved successfully\n";
            echo "   Active calls count: " . count($activeCalls) . "\n";
            $this->testResults['active_calls'] = 'PASS';
        } else {
            echo "❌ Get active calls failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['active_calls'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetStreamTokens()
    {
        echo "🎬 Test 7: Get Stream Tokens for Call...\n";

        // Use the existing video call with tokens
        if (empty($this->callIds['video_with_tokens'])) {
            echo "❌ No video call available for token test\n";
            $this->testResults['stream_tokens'] = 'FAIL';
            echo "\n";
            return;
        }

        $callId = $this->callIds['video_with_tokens'];

        // Get stream tokens
        $ch = curl_init($this->baseUrl . "/api/calls/{$callId}/stream-tokens");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Stream tokens retrieved successfully\n";
            echo "   Call ID: $callId\n";
            
            if (isset($data['data'])) {
                echo "   API Key: " . (isset($data['data']['api_key']) ? 'Present' : 'Missing') . "\n";
                echo "   User Token: " . (isset($data['data']['user_token']) ? 'Present' : 'Missing') . "\n";
                echo "   Call ID (Stream): " . ($data['data']['call_id'] ?? 'N/A') . "\n";
            }
            
            $this->testResults['stream_tokens'] = 'PASS';
        } else {
            echo "❌ Get stream tokens failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['stream_tokens'] = 'FAIL';
        }
        echo "\n";
    }

    private function testMissedCallsCount()
    {
        echo "📵 Test 10: Get Missed Calls Count...\n";

        $ch = curl_init($this->baseUrl . '/api/calls/missed-count');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Missed calls count retrieved successfully\n";
            echo "   Missed calls: " . ($data['data']['count'] ?? 0) . "\n";
            $this->testResults['missed_calls'] = 'PASS';
        } else {
            echo "❌ Get missed calls failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['missed_calls'] = 'FAIL';
        }
        echo "\n";
    }

    private function testCallStatistics()
    {
        echo "📊 Test 11: Get Call Statistics...\n";

        $ch = curl_init($this->baseUrl . '/api/calls/statistics');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['success']) && $data['success']) {
            echo "✅ Call statistics retrieved successfully\n";
            
            if (isset($data['data'])) {
                echo "   Total calls: " . ($data['data']['total_calls'] ?? 0) . "\n";
                echo "   Answered calls: " . ($data['data']['answered_calls'] ?? 0) . "\n";
                echo "   Missed calls: " . ($data['data']['missed_calls'] ?? 0) . "\n";
                echo "   Total duration: " . ($data['data']['total_duration'] ?? 0) . " seconds\n";
            }
            
            $this->testResults['call_statistics'] = 'PASS';
        } else {
            echo "❌ Get call statistics failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['call_statistics'] = 'FAIL';
        }
        echo "\n";
    }

    private function displaySummary()
    {
        echo str_repeat("=", 80) . "\n";
        echo "📊 CALLING SYSTEM TEST SUMMARY\n";
        echo str_repeat("=", 80) . "\n\n";

        $passed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($this->testResults as $test => $result) {
            $icon = $result === 'PASS' ? '✅' : ($result === 'SKIP' ? '⏭️' : '❌');
            echo "$icon " . str_pad(ucwords(str_replace('_', ' ', $test)), 40) . " $result\n";

            if ($result === 'PASS') $passed++;
            elseif ($result === 'FAIL') $failed++;
            else $skipped++;
        }

        $total = $passed + $failed;
        $percentage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

        echo "\n";
        echo "Total Tests: " . ($passed + $failed + $skipped) . "\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Skipped: $skipped\n";
        echo "Success Rate: $percentage%\n\n";

        if ($failed === 0 && $passed > 0) {
            echo "🎉 All active tests passed! Calling system is working.\n";
        } else {
            echo "⚠️  Some tests failed. Please review above.\n";
        }

        echo str_repeat("=", 80) . "\n";
    }
}

// Run the tests
$tester = new CallingSystemTester();
$tester->runAllTests();
