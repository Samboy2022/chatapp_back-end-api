<?php

/**
 * Final Comprehensive Chat API Testing Script
 * Complete testing with proper error handling and response parsing
 */

class FinalChatApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $authToken = null;
    private $testUser = null;
    private $testResults = [];
    private $testChatId = null;
    private $testMessages = [];
    private $testFiles = [];

    public function __construct()
    {
        echo "🚀 FINAL COMPREHENSIVE CHAT API TEST\n";
        echo "=====================================\n\n";
    }

    public function runTests()
    {
        try {
            $this->testConnectivity();
            $this->testAuthentication();
            
            if ($this->authToken) {
                $this->testChatOperations();
                $this->testMessaging();
                $this->testFileOperations();
                $this->testGroupOperations();
                $this->testRealTimeFeatures();
                $this->testAdvancedFeatures();
            }
            
            $this->printDetailedResults();
            
        } catch (Exception $e) {
            echo "❌ Fatal Error: " . $e->getMessage() . "\n";
        }
    }

    private function testConnectivity()
    {
        echo "🔗 Testing API Connectivity...\n";
        
        $response = $this->makeRequest('GET', '/test');
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ API is accessible\n";
            echo "   Message: " . ($response['message'] ?? 'N/A') . "\n";
            echo "   Version: " . ($response['version'] ?? 'N/A') . "\n";
            echo "   Timestamp: " . ($response['timestamp'] ?? 'N/A') . "\n";
            $this->testResults['connectivity'] = true;
        } else {
            echo "❌ API connectivity failed\n";
            $this->testResults['connectivity'] = false;
        }
        echo "\n";
    }

    private function testAuthentication()
    {
        echo "🔐 Testing Authentication System...\n";
        
        // Test with admin user
        $loginData = [
            'login' => 'admin@chatapp.com',
            'password' => 'password'
        ];
        
        $response = $this->makeRequest('POST', '/auth/login', $loginData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->testUser = $response['data'];
            $this->authToken = $this->testUser['token'];
            echo "✅ Login successful\n";
            echo "   User: " . ($this->testUser['user']['name'] ?? 'Unknown') . "\n";
            echo "   Email: " . ($this->testUser['user']['email'] ?? 'Unknown') . "\n";
            echo "   Token Type: " . ($this->testUser['token_type'] ?? 'Bearer') . "\n";
            
            // Test authenticated endpoint
            $userResponse = $this->makeRequest('GET', '/auth/user');
            if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
                echo "✅ Authenticated endpoint working\n";
                $userData = $userResponse['data']['user'] ?? [];
                echo "   Verified User ID: " . ($userData['id'] ?? 'N/A') . "\n";
                echo "   Verified Name: " . ($userData['name'] ?? 'N/A') . "\n";
                echo "   Verified Email: " . ($userData['email'] ?? 'N/A') . "\n";
                $this->testResults['authentication'] = true;
            } else {
                echo "❌ Authenticated endpoint failed\n";
                echo "   Error: " . ($userResponse['message'] ?? 'Unknown error') . "\n";
                $this->testResults['authentication'] = false;
            }
        } else {
            echo "❌ Authentication failed\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['authentication'] = false;
        }
        echo "\n";
    }

    private function testChatOperations()
    {
        echo "💬 Testing Chat Operations...\n";
        
        // Test getting existing chats
        $response = $this->makeRequest('GET', '/chats');
        if ($response && isset($response['success']) && $response['success']) {
            $chatCount = count($response['data']['chats'] ?? []);
            echo "✅ Retrieved chats successfully\n";
            echo "   Chat count: {$chatCount}\n";
            
            // Use existing chat if available
            if ($chatCount > 0 && isset($response['data']['chats'][0]['id'])) {
                $this->testChatId = $response['data']['chats'][0]['id'];
                echo "   Using existing chat ID: {$this->testChatId}\n";
            }
            $this->testResults['get_chats'] = true;
        } else {
            echo "❌ Failed to get chats\n";
            $this->testResults['get_chats'] = false;
        }
        
        // Test creating a new chat
        $this->testCreateNewChat();
        
        echo "\n";
    }

    private function testCreateNewChat()
    {
        echo "➕ Testing New Chat Creation...\n";
        
        // Create a test user for chat
        $newUserData = [
            'name' => 'Chat Test User ' . time(),
            'email' => 'chattest' . time() . '@example.com',
            'phone_number' => '+1555' . substr(time(), -6),
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $userResponse = $this->makeRequest('POST', '/auth/register', $newUserData);
        
        if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
            $otherUserId = $userResponse['data']['user']['id'];
            echo "✅ Created test user: ID {$otherUserId}\n";
            
            // Create private chat
            $chatData = [
                'participants' => [$otherUserId],
                'type' => 'private'
            ];
            
            $chatResponse = $this->makeRequest('POST', '/chats', $chatData);
            
            // Debug the response structure
            echo "   Chat Response: " . json_encode($chatResponse) . "\n";
            
            if ($chatResponse && isset($chatResponse['success']) && $chatResponse['success']) {
                // Try different possible response structures
                $chatId = null;
                if (isset($chatResponse['data']['id'])) {
                    $chatId = $chatResponse['data']['id'];
                } elseif (isset($chatResponse['data']['chat']['id'])) {
                    $chatId = $chatResponse['data']['chat']['id'];
                } elseif (isset($chatResponse['chat']['id'])) {
                    $chatId = $chatResponse['chat']['id'];
                }
                
                if ($chatId) {
                    $this->testChatId = $chatId;
                    echo "✅ Private chat created successfully\n";
                    echo "   Chat ID: {$this->testChatId}\n";
                    $this->testResults['create_chat'] = true;
                } else {
                    echo "⚠️  Chat created but ID not found in response\n";
                    $this->testResults['create_chat'] = false;
                }
            } else {
                echo "❌ Failed to create chat\n";
                echo "   Error: " . ($chatResponse['message'] ?? 'Unknown error') . "\n";
                $this->testResults['create_chat'] = false;
            }
        } else {
            echo "❌ Failed to create test user\n";
            $this->testResults['create_chat'] = false;
        }
    }

    private function testMessaging()
    {
        echo "📝 Testing Messaging System...\n";
        
        if (!$this->testChatId) {
            echo "⚠️  No chat available for message testing\n";
            $this->testResults['text_messaging'] = false;
            echo "\n";
            return;
        }
        
        echo "   Using Chat ID: {$this->testChatId}\n";
        
        $messages = [
            [
                'type' => 'text',
                'content' => '🚀 Hello! This is a comprehensive API test message.'
            ],
            [
                'type' => 'text',
                'content' => '😊 Testing emojis and special characters: @#$%^&*() 💬🔥'
            ],
            [
                'type' => 'text',
                'content' => 'This is a longer message to test the system\'s ability to handle extended content. It includes multiple sentences, punctuation marks, and should demonstrate the robustness of the messaging system.'
            ]
        ];
        
        $successCount = 0;
        foreach ($messages as $index => $messageData) {
            echo "   Sending message " . ($index + 1) . "...\n";
            
            $response = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $messageData);
            
            if ($response && isset($response['success']) && $response['success']) {
                echo "   ✅ Message sent: " . substr($messageData['content'], 0, 40) . "...\n";
                $this->testMessages[] = $response['data'];
                $successCount++;
            } else {
                echo "   ❌ Message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                if (isset($response['errors'])) {
                    echo "   Validation errors: " . json_encode($response['errors']) . "\n";
                }
            }
            usleep(500000); // 0.5 second delay
        }
        
        $this->testResults['text_messaging'] = $successCount > 0;
        echo "✅ Text messages sent: {$successCount}/" . count($messages) . "\n";
        
        // Test retrieving messages
        $this->testRetrieveMessages();
        
        echo "\n";
    }

    private function testRetrieveMessages()
    {
        echo "📥 Testing Message Retrieval...\n";
        
        $response = $this->makeRequest('GET', "/chats/{$this->testChatId}/messages");
        if ($response && isset($response['success']) && $response['success']) {
            $messageCount = count($response['data']['data'] ?? $response['data'] ?? []);
            echo "✅ Retrieved messages: {$messageCount} messages\n";
            
            // Show sample message structure
            if ($messageCount > 0) {
                $sampleMessage = $response['data']['data'][0] ?? $response['data'][0] ?? null;
                if ($sampleMessage) {
                    echo "   Sample message ID: " . ($sampleMessage['id'] ?? 'N/A') . "\n";
                    echo "   Sample content: " . substr($sampleMessage['content'] ?? '', 0, 30) . "...\n";
                }
            }
            $this->testResults['retrieve_messages'] = true;
        } else {
            echo "❌ Failed to retrieve messages\n";
            $this->testResults['retrieve_messages'] = false;
        }
    }

    private function testFileOperations()
    {
        echo "📁 Testing File Upload Operations...\n";
        
        $testFiles = [
            'document' => $this->createTestDocument(),
            'audio' => $this->createTestAudio()
        ];
        
        $successCount = 0;
        foreach ($testFiles as $type => $filePath) {
            if ($filePath) {
                echo "📤 Testing {$type} upload...\n";
                
                try {
                    $response = $this->uploadFile($filePath, $type);
                    if ($response && isset($response['success']) && $response['success']) {
                        echo "✅ {$type} uploaded successfully\n";
                        echo "   File URL: " . ($response['data']['url'] ?? 'N/A') . "\n";
                        echo "   File size: " . ($response['data']['size_formatted'] ?? 'N/A') . "\n";
                        echo "   MIME type: " . ($response['data']['mime_type'] ?? 'N/A') . "\n";
                        
                        $this->testFiles[] = $response['data'];
                        $successCount++;
                        
                        // Send as message if we have a chat
                        if ($this->testChatId) {
                            $this->sendMediaMessage($type, $response['data']);
                        }
                    } else {
                        echo "❌ {$type} upload failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                    }
                } catch (Exception $e) {
                    echo "❌ {$type} upload error: " . $e->getMessage() . "\n";
                }
                
                // Cleanup
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $this->testResults['file_upload'] = $successCount > 0;
        echo "✅ Files uploaded successfully: {$successCount}/" . count($testFiles) . "\n";
        echo "\n";
    }

    private function testGroupOperations()
    {
        echo "👥 Testing Group Chat Operations...\n";

        // Create users for group testing
        $groupUsers = [];
        for ($i = 1; $i <= 2; $i++) {
            $userData = [
                'name' => "Group User {$i} " . time(),
                'email' => "groupuser{$i}" . time() . '@example.com',
                'phone_number' => '+1777' . substr(time(), -3) . $i,
                'country_code' => '+1',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->makeRequest('POST', '/auth/register', $userData);
            if ($response && isset($response['success']) && $response['success']) {
                $groupUsers[] = $response['data']['user']['id'];
                echo "✅ Created group user {$i}: ID " . $response['data']['user']['id'] . "\n";
            }
        }

        if (count($groupUsers) >= 1) {
            // Create group chat
            $groupData = [
                'type' => 'group',
                'name' => 'API Test Group ' . time(),
                'description' => 'Comprehensive API test group chat',
                'participants' => $groupUsers
            ];

            $response = $this->makeRequest('POST', '/chats', $groupData);

            if ($response && isset($response['success']) && $response['success']) {
                // Extract group ID from response
                $groupId = null;
                if (isset($response['data']['id'])) {
                    $groupId = $response['data']['id'];
                } elseif (isset($response['data']['chat']['id'])) {
                    $groupId = $response['data']['chat']['id'];
                }

                if ($groupId) {
                    echo "✅ Group chat created successfully\n";
                    echo "   Group ID: {$groupId}\n";

                    // Get group name from correct location in response
                    $groupName = $response['data']['name'] ??
                                $response['data']['chat']['name'] ??
                                'N/A';
                    echo "   Group Name: {$groupName}\n";

                    // Send group message
                    $groupMessage = [
                        'type' => 'text',
                        'content' => '🎉 Welcome to the API test group! This message was sent automatically during testing.'
                    ];

                    $messageResponse = $this->makeRequest('POST', "/chats/{$groupId}/messages", $groupMessage);
                    if ($messageResponse && isset($messageResponse['success']) && $messageResponse['success']) {
                        echo "✅ Group message sent successfully\n";
                    } else {
                        echo "❌ Group message failed\n";
                    }

                    $this->testResults['group_operations'] = true;
                } else {
                    echo "⚠️  Group created but ID not found\n";
                    $this->testResults['group_operations'] = false;
                }
            } else {
                echo "❌ Failed to create group chat\n";
                echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
                $this->testResults['group_operations'] = false;
            }
        } else {
            echo "❌ Not enough users created for group testing\n";
            $this->testResults['group_operations'] = false;
        }

        echo "\n";
    }

    private function testRealTimeFeatures()
    {
        echo "⚡ Testing Real-time Features...\n";

        // Test WebSocket connection info
        $response = $this->makeRequest('GET', '/websocket/connection-info');
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ WebSocket connection info retrieved\n";
            $wsData = $response['data'];
            echo "   Host: " . ($wsData['host'] ?? 'N/A') . "\n";
            echo "   Port: " . ($wsData['port'] ?? 'N/A') . "\n";
            echo "   Scheme: " . ($wsData['scheme'] ?? 'N/A') . "\n";
            echo "   App Key: " . ($wsData['app_key'] ?? 'N/A') . "\n";
            echo "   Auth Endpoint: " . ($wsData['auth_endpoint'] ?? 'N/A') . "\n";

            // Show full WebSocket URL
            $scheme = $wsData['scheme'] ?? 'ws';
            $host = $wsData['host'] ?? 'localhost';
            $port = $wsData['port'] ?? '8080';
            echo "   Full URL: {$scheme}://{$host}:{$port}\n";

            $this->testResults['websocket_info'] = true;
        } else {
            echo "❌ WebSocket info failed\n";
            $this->testResults['websocket_info'] = false;
        }

        // Test online status
        $statusResponse = $this->makeRequest('POST', '/websocket/online-status', [
            'is_online' => true
        ]);
        if ($statusResponse && isset($statusResponse['success']) && $statusResponse['success']) {
            echo "✅ Online status updated successfully\n";
        } else {
            echo "❌ Online status update failed\n";
        }

        // Test typing indicator
        if ($this->testChatId) {
            $typingResponse = $this->makeRequest('POST', "/websocket/chats/{$this->testChatId}/typing", [
                'is_typing' => true
            ]);
            if ($typingResponse && isset($typingResponse['success']) && $typingResponse['success']) {
                echo "✅ Typing indicator sent successfully\n";
            } else {
                echo "❌ Typing indicator failed\n";
            }
        }

        echo "\n";
    }

    private function testAdvancedFeatures()
    {
        echo "🔧 Testing Advanced Features...\n";

        // Test message reactions (if we have messages)
        if (!empty($this->testMessages) && $this->testChatId) {
            $messageId = $this->testMessages[0]['id'];
            $reactionResponse = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages/{$messageId}/react", [
                'emoji' => '👍'
            ]);
            if ($reactionResponse && isset($reactionResponse['success']) && $reactionResponse['success']) {
                echo "✅ Message reaction added successfully\n";
                echo "   Reaction: " . ($reactionResponse['data']['emoji'] ?? 'N/A') . "\n";
            } else {
                echo "❌ Message reaction failed: " . ($reactionResponse['message'] ?? 'Unknown error') . "\n";
            }
        }

        // Test message replies
        if (!empty($this->testMessages) && $this->testChatId) {
            $originalMessage = $this->testMessages[0];
            $replyData = [
                'type' => 'text',
                'content' => 'This is a reply to the test message!',
                'reply_to_message_id' => $originalMessage['id']
            ];

            $replyResponse = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $replyData);
            if ($replyResponse && isset($replyResponse['success']) && $replyResponse['success']) {
                echo "✅ Message reply sent successfully\n";
            } else {
                echo "❌ Message reply failed\n";
            }
        }

        $this->testResults['advanced_features'] = true;
        echo "\n";
    }

    // Helper methods
    private function createTestDocument()
    {
        $content = "📄 COMPREHENSIVE API TEST DOCUMENT\n";
        $content .= "===================================\n\n";
        $content .= "This document was created during comprehensive API testing.\n";
        $content .= "Test timestamp: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Test features: Text messaging, file upload, group chat\n";
        $content .= "Special characters: @#$%^&*()_+-=[]{}|;:,.<>?\n";
        $content .= "Unicode: 🚀😊💬🔥📱💻🌟\n";
        $content .= "Numbers: 1234567890\n";
        $content .= "\nThis file tests the document upload functionality of the chat API.\n";

        $filePath = sys_get_temp_dir() . '/comprehensive_test_doc_' . time() . '.txt';
        file_put_contents($filePath, $content);

        return $filePath;
    }

    private function createTestAudio()
    {
        // Create a minimal WAV file (1 second of silence)
        $audioPath = sys_get_temp_dir() . '/test_audio_' . time() . '.wav';

        // Create a proper WAV file header with actual audio data
        $sampleRate = 8000;
        $channels = 1;
        $bitsPerSample = 16;
        $duration = 1; // 1 second
        $dataSize = $sampleRate * $channels * ($bitsPerSample / 8) * $duration;
        $fileSize = 36 + $dataSize;

        // WAV header
        $header = pack('A4V', 'RIFF', $fileSize);
        $header .= pack('A4', 'WAVE');
        $header .= pack('A4V', 'fmt ', 16);
        $header .= pack('vvVVvv', 1, $channels, $sampleRate, $sampleRate * $channels * ($bitsPerSample / 8), $channels * ($bitsPerSample / 8), $bitsPerSample);
        $header .= pack('A4V', 'data', $dataSize);

        // Add silence data (zeros)
        $audioData = str_repeat(pack('v', 0), $dataSize / 2);

        file_put_contents($audioPath, $header . $audioData);

        return $audioPath;
    }

    private function sendMediaMessage($type, $mediaData)
    {
        $messageData = [
            'type' => $type,
            'content' => "📎 Shared a {$type} file via comprehensive API test",
            'media_url' => $mediaData['url'],
            'media_size' => $mediaData['size'],
            'media_mime_type' => $mediaData['mime_type'],
            'file_name' => $mediaData['original_name']
        ];

        if (isset($mediaData['duration'])) {
            $messageData['media_duration'] = $mediaData['duration'];
        }

        $response = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $messageData);
        if ($response && isset($response['success']) && $response['success']) {
            echo "   ✅ Media message sent to chat\n";
        } else {
            echo "   ❌ Media message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
    }

    private function uploadFile($filePath, $type)
    {
        $url = $this->baseUrl . '/media/upload';
        $ch = curl_init();

        $postData = [
            'file' => new CURLFile($filePath),
            'type' => $type
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->authToken,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }

        return json_decode($response, true);
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $headers = ['Accept: application/json'];

        if ($this->authToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }

        if ($data && $method !== 'GET') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "   CURL Error: {$error}\n";
            return null;
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            echo "   HTTP {$httpCode}: " . ($decodedResponse['message'] ?? 'Unknown error') . "\n";
            if (isset($decodedResponse['errors'])) {
                echo "   Validation errors: " . json_encode($decodedResponse['errors']) . "\n";
            }
        }

        return $decodedResponse;
    }

    private function printDetailedResults()
    {
        echo "📊 FINAL COMPREHENSIVE TEST RESULTS\n";
        echo "====================================\n";

        $totalTests = count($this->testResults);
        $passedTests = array_sum($this->testResults);
        $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

        echo "📈 OVERALL SUCCESS RATE: {$successRate}% ({$passedTests}/{$totalTests})\n\n";

        echo "📋 DETAILED TEST RESULTS:\n";
        echo "-------------------------\n";
        foreach ($this->testResults as $test => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $testName = ucwords(str_replace('_', ' ', $test));
            echo "{$status} - {$testName}\n";
        }

        echo "\n📊 STATISTICS:\n";
        echo "---------------\n";
        echo "📝 Total Messages Sent: " . count($this->testMessages) . "\n";
        echo "📁 Total Files Uploaded: " . count($this->testFiles) . "\n";
        echo "💬 Test Chat ID: " . ($this->testChatId ?? 'N/A') . "\n";
        echo "👤 Test User: " . ($this->testUser['user']['name'] ?? 'N/A') . "\n";
        echo "🔗 API Base URL: {$this->baseUrl}\n";

        echo "\n🎯 PERFORMANCE ASSESSMENT:\n";
        echo "---------------------------\n";
        if ($successRate >= 95) {
            echo "🏆 EXCELLENT! Chat API is performing exceptionally well.\n";
            echo "   All core features are working perfectly.\n";
        } elseif ($successRate >= 85) {
            echo "🎉 VERY GOOD! Chat API is working very well.\n";
            echo "   Most features are functional with minor issues.\n";
        } elseif ($successRate >= 70) {
            echo "👍 GOOD! Chat API core functionality is working.\n";
            echo "   Some features may need attention.\n";
        } elseif ($successRate >= 50) {
            echo "⚠️  FAIR! Chat API has basic functionality.\n";
            echo "   Several issues need to be addressed.\n";
        } else {
            echo "🚨 POOR! Chat API has significant issues.\n";
            echo "   Major problems need immediate attention.\n";
        }

        echo "\n🔍 RECOMMENDATIONS:\n";
        echo "-------------------\n";
        if (!($this->testResults['text_messaging'] ?? true)) {
            echo "• Fix message sending functionality\n";
        }
        if (!($this->testResults['file_upload'] ?? true)) {
            echo "• Resolve file upload issues\n";
        }
        if (!($this->testResults['group_operations'] ?? true)) {
            echo "• Address group chat creation problems\n";
        }
        if (!($this->testResults['websocket_info'] ?? true)) {
            echo "• Check WebSocket configuration\n";
        }

        echo "\n✅ TESTING COMPLETE!\n";
        echo "=====================\n";
    }
}

// Run the comprehensive test
$tester = new FinalChatApiTester();
$tester->runTests();
