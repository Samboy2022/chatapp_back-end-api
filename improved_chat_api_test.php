<?php

/**
 * Improved Chat API Testing Script
 * Uses existing users and comprehensive testing
 */

class ImprovedChatApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $authToken = null;
    private $testUser = null;
    private $testResults = [];
    private $testChatId = null;
    private $testMessages = [];

    public function __construct()
    {
        echo "🚀 Improved Chat API Testing\n";
        echo "=============================\n\n";
    }

    public function runTests()
    {
        try {
            // Test basic connectivity
            $this->testConnectivity();
            
            // Test authentication with existing user
            $this->testAuthentication();
            
            // Test chat functionality
            if ($this->authToken) {
                $this->testChatFunctionality();
                $this->testMessaging();
                $this->testFileUpload();
                $this->testGroupChat();
                $this->testRealTimeFeatures();
            }
            
            $this->printResults();
            
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
            $this->testResults['connectivity'] = true;
        } else {
            echo "❌ API connectivity failed\n";
            $this->testResults['connectivity'] = false;
        }
        echo "\n";
    }

    private function testAuthentication()
    {
        echo "🔐 Testing Authentication...\n";
        
        // Try with admin user first
        $loginData = [
            'login' => 'admin@chatapp.com',
            'password' => 'password'
        ];
        
        $response = $this->makeRequest('POST', '/auth/login', $loginData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->testUser = $response['data'];
            $this->authToken = $this->testUser['token'];
            echo "✅ Authentication successful\n";
            echo "   User: " . ($this->testUser['user']['name'] ?? 'Unknown') . "\n";
            echo "   Email: " . ($this->testUser['user']['email'] ?? 'Unknown') . "\n";
            $this->testResults['authentication'] = true;
            
            // Test authenticated endpoint
            $userResponse = $this->makeRequest('GET', '/auth/user');
            if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
                echo "✅ Authenticated endpoint working\n";
            }
        } else {
            echo "❌ Authentication failed\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['authentication'] = false;
        }
        echo "\n";
    }

    private function testChatFunctionality()
    {
        echo "💬 Testing Chat Functionality...\n";
        
        // Test getting chats
        $response = $this->makeRequest('GET', '/chats');
        if ($response && isset($response['success']) && $response['success']) {
            $chatCount = count($response['data']['chats'] ?? []);
            echo "✅ Retrieved chats successfully\n";
            echo "   Chat count: {$chatCount}\n";
            $this->testResults['get_chats'] = true;
            
            // If there are existing chats, use the first one for testing
            if ($chatCount > 0) {
                $this->testChatId = $response['data']['chats'][0]['id'];
                echo "   Using existing chat ID: {$this->testChatId}\n";
            }
        } else {
            echo "❌ Failed to get chats: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['get_chats'] = false;
        }
        
        // Test creating a new chat if we don't have one
        if (!$this->testChatId) {
            $this->testCreateChat();
        }
        
        echo "\n";
    }

    private function testCreateChat()
    {
        echo "➕ Testing Create Chat...\n";
        
        // Create a new user for chat testing
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
            echo "✅ Created new user for chat: ID {$otherUserId}\n";
            
            // Create a private chat
            $chatData = [
                'participants' => [$otherUserId],
                'type' => 'private'
            ];
            
            $response = $this->makeRequest('POST', '/chats', $chatData);
            if ($response && isset($response['success']) && $response['success']) {
                $this->testChatId = $response['data']['id'];
                echo "✅ Private chat created successfully\n";
                echo "   Chat ID: {$this->testChatId}\n";
                $this->testResults['create_chat'] = true;
            } else {
                echo "❌ Failed to create chat: " . ($response['message'] ?? 'Unknown error') . "\n";
                $this->testResults['create_chat'] = false;
            }
        } else {
            echo "❌ Failed to create new user for chat\n";
            $this->testResults['create_chat'] = false;
        }
    }

    private function testMessaging()
    {
        echo "📝 Testing Messaging...\n";
        
        if (!$this->testChatId) {
            echo "⚠️  No chat available for message testing\n";
            $this->testResults['messaging'] = false;
            echo "\n";
            return;
        }
        
        $messages = [
            [
                'message_type' => 'text',
                'content' => 'Hello! This is a test message from the API test.'
            ],
            [
                'message_type' => 'text',
                'content' => 'Testing emojis and special characters: 😊🚀💬 @#$%'
            ],
            [
                'message_type' => 'text',
                'content' => 'This is a longer message to test the system\'s ability to handle more content. It contains multiple sentences and should be processed correctly by the messaging system.'
            ]
        ];
        
        $successCount = 0;
        foreach ($messages as $messageData) {
            $response = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $messageData);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Message sent: " . substr($messageData['content'], 0, 40) . "...\n";
                $this->testMessages[] = $response['data'];
                $successCount++;
            } else {
                echo "❌ Message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
            usleep(300000); // 0.3 second delay
        }
        
        $this->testResults['messaging'] = $successCount > 0;
        echo "   Messages sent successfully: {$successCount}/" . count($messages) . "\n";
        
        // Test getting messages
        $response = $this->makeRequest('GET', "/chats/{$this->testChatId}/messages");
        if ($response && isset($response['success']) && $response['success']) {
            $messageCount = count($response['data']['data'] ?? []);
            echo "✅ Retrieved messages: {$messageCount} messages\n";
        } else {
            echo "❌ Failed to retrieve messages\n";
        }
        
        echo "\n";
    }

    private function testFileUpload()
    {
        echo "📁 Testing File Upload...\n";
        
        // Create test files
        $testFiles = [
            'document' => $this->createTestDocument(),
            'image' => $this->createTestImage()
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
        echo "   Files uploaded successfully: {$successCount}/" . count($testFiles) . "\n";
        echo "\n";
    }

    private function testGroupChat()
    {
        echo "👥 Testing Group Chat...\n";
        
        // Create another user for group testing
        $groupUserData = [
            'name' => 'Group Test User ' . time(),
            'email' => 'grouptest' . time() . '@example.com',
            'phone_number' => '+1666' . substr(time(), -6),
            'country_code' => '+1',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $userResponse = $this->makeRequest('POST', '/auth/register', $groupUserData);
        
        if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
            $groupUserId = $userResponse['data']['user']['id'];
            
            // Create a group chat
            $groupData = [
                'type' => 'group',
                'name' => 'API Test Group ' . time(),
                'description' => 'This is a test group created by the API test script',
                'participants' => [$groupUserId]
            ];
            
            $response = $this->makeRequest('POST', '/chats', $groupData);
            if ($response && isset($response['success']) && $response['success']) {
                $groupChatId = $response['data']['id'];
                echo "✅ Group chat created successfully\n";
                echo "   Group ID: {$groupChatId}\n";
                echo "   Group Name: " . ($response['data']['name'] ?? 'N/A') . "\n";
                
                // Send a message to the group
                $groupMessageData = [
                    'message_type' => 'text',
                    'content' => 'Welcome to the test group! This is an automated message from the API test.'
                ];
                
                $messageResponse = $this->makeRequest('POST', "/chats/{$groupChatId}/messages", $groupMessageData);
                if ($messageResponse && isset($messageResponse['success']) && $messageResponse['success']) {
                    echo "✅ Group message sent successfully\n";
                }
                
                $this->testResults['group_chat'] = true;
            } else {
                echo "❌ Failed to create group chat: " . ($response['message'] ?? 'Unknown error') . "\n";
                $this->testResults['group_chat'] = false;
            }
        } else {
            echo "❌ Failed to create user for group testing\n";
            $this->testResults['group_chat'] = false;
        }
        
        echo "\n";
    }

    private function testRealTimeFeatures()
    {
        echo "⚡ Testing Real-time Features...\n";

        // Test WebSocket connection info
        $response = $this->makeRequest('GET', '/websocket/connection-info');
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ WebSocket info retrieved\n";
            echo "   Host: " . ($response['data']['host'] ?? 'N/A') . "\n";
            echo "   Port: " . ($response['data']['port'] ?? 'N/A') . "\n";
            echo "   App Key: " . ($response['data']['app_key'] ?? 'N/A') . "\n";
            $this->testResults['websocket_info'] = true;
        } else {
            echo "❌ WebSocket info failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['websocket_info'] = false;
        }

        // Test online status update
        $response = $this->makeRequest('POST', '/websocket/online-status', [
            'is_online' => true
        ]);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Online status updated\n";
        } else {
            echo "❌ Online status update failed\n";
        }

        // Test typing indicator
        if ($this->testChatId) {
            $response = $this->makeRequest('POST', "/websocket/chats/{$this->testChatId}/typing", [
                'is_typing' => true
            ]);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Typing indicator sent\n";
            } else {
                echo "❌ Typing indicator failed\n";
            }
        }

        echo "\n";
    }

    private function createTestDocument()
    {
        $content = "API Test Document\n";
        $content .= "==================\n";
        $content .= "This is a test document created by the chat API test script.\n";
        $content .= "It contains multiple lines and various characters.\n";
        $content .= "Special characters: @#$%^&*()\n";
        $content .= "Numbers: 1234567890\n";
        $content .= "Date: " . date('Y-m-d H:i:s') . "\n";

        $filePath = sys_get_temp_dir() . '/api_test_document_' . time() . '.txt';
        file_put_contents($filePath, $content);

        return $filePath;
    }

    private function createTestImage()
    {
        // Create a simple 1x1 PNG image
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jU8j8wAAAABJRU5ErkJggg==');
        $filePath = sys_get_temp_dir() . '/api_test_image_' . time() . '.png';
        file_put_contents($filePath, $imageData);

        return $filePath;
    }

    private function sendMediaMessage($type, $mediaData)
    {
        $messageData = [
            'message_type' => $type,
            'content' => "Shared a {$type} file via API test",
            'media_url' => $mediaData['url'],
            'media_size' => $mediaData['size'],
            'media_mime_type' => $mediaData['mime_type'],
            'file_name' => $mediaData['original_name']
        ];

        $response = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $messageData);
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ Media message sent to chat\n";
        } else {
            echo "❌ Media message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
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
            echo "CURL Error: {$error}\n";
            return null;
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            echo "HTTP {$httpCode}: " . ($decodedResponse['message'] ?? 'Unknown error') . "\n";
            if (isset($decodedResponse['errors'])) {
                echo "Validation errors: " . json_encode($decodedResponse['errors']) . "\n";
            }
        }

        return $decodedResponse;
    }

    private function printResults()
    {
        echo "📊 COMPREHENSIVE TEST RESULTS\n";
        echo "==============================\n";

        $totalTests = count($this->testResults);
        $passedTests = array_sum($this->testResults);
        $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;

        foreach ($this->testResults as $test => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $testName = ucwords(str_replace('_', ' ', $test));
            echo "{$status} - {$testName}\n";
        }

        echo "\n";
        echo "📈 Overall Success Rate: {$successRate}% ({$passedTests}/{$totalTests})\n";
        echo "📝 Total Messages Sent: " . count($this->testMessages) . "\n";

        if ($successRate >= 90) {
            echo "🎉 Excellent! Chat API is working perfectly.\n";
        } elseif ($successRate >= 75) {
            echo "👍 Very Good! Most features are working well.\n";
        } elseif ($successRate >= 60) {
            echo "👌 Good! Core features are functional.\n";
        } elseif ($successRate >= 40) {
            echo "⚠️  Fair. Some issues need attention.\n";
        } else {
            echo "🚨 Poor. Significant issues detected.\n";
        }

        echo "\n";
        echo "🔗 API Base URL: {$this->baseUrl}\n";
        echo "👤 Test User: " . ($this->testUser['user']['name'] ?? 'N/A') . "\n";
        echo "💬 Test Chat ID: " . ($this->testChatId ?? 'N/A') . "\n";
        echo "\n";
    }
}

// Run the tests
$tester = new ImprovedChatApiTester();
$tester->runTests();
