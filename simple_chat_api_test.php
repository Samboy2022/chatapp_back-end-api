<?php

/**
 * Simple Chat API Testing Script
 * Direct API testing with better error handling
 */

class SimpleChatApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $authToken = null;
    private $testUser = null;
    private $testResults = [];

    public function __construct()
    {
        echo "🚀 Simple Chat API Testing\n";
        echo "==========================\n\n";
    }

    public function runTests()
    {
        try {
            // Test basic connectivity
            $this->testConnectivity();
            
            // Test or create user
            $this->setupTestUser();
            
            // Test authentication
            $this->testAuthentication();
            
            // Test chat functionality
            if ($this->authToken) {
                $this->testChatFunctionality();
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
            $this->testResults['connectivity'] = true;
        } else {
            echo "❌ API connectivity failed\n";
            $this->testResults['connectivity'] = false;
        }
        echo "\n";
    }

    private function setupTestUser()
    {
        echo "👤 Setting up test user...\n";
        
        // Try to register a new user
        $userData = [
            'name' => 'Test User ' . time(),
            'email' => 'testuser' . time() . '@example.com',
            'phone_number' => '+1234567' . substr(time(), -3),
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->makeRequest('POST', '/auth/register', $userData);
        
        if ($response && isset($response['success']) && $response['success']) {
            $this->testUser = $response['data'];
            echo "✅ New user registered: " . $userData['name'] . "\n";
            echo "   Email: " . $userData['email'] . "\n";
            $this->testResults['user_creation'] = true;
        } else {
            echo "⚠️  Registration failed, trying with existing user...\n";
            
            // Try with a simple existing user
            $loginData = [
                'login' => 'admin@example.com',
                'password' => 'password'
            ];
            
            $loginResponse = $this->makeRequest('POST', '/auth/login', $loginData);
            if ($loginResponse && isset($loginResponse['success']) && $loginResponse['success']) {
                $this->testUser = $loginResponse['data'];
                echo "✅ Logged in with existing user\n";
                $this->testResults['user_creation'] = true;
            } else {
                echo "❌ Could not create or login user\n";
                echo "   Registration error: " . ($response['message'] ?? 'Unknown') . "\n";
                echo "   Login error: " . ($loginResponse['message'] ?? 'Unknown') . "\n";
                $this->testResults['user_creation'] = false;
            }
        }
        echo "\n";
    }

    private function testAuthentication()
    {
        echo "🔐 Testing Authentication...\n";
        
        if ($this->testUser && isset($this->testUser['token'])) {
            $this->authToken = $this->testUser['token'];
            
            // Test authenticated endpoint
            $response = $this->makeRequest('GET', '/auth/user');
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Authentication working\n";
                echo "   User: " . ($response['data']['name'] ?? 'Unknown') . "\n";
                $this->testResults['authentication'] = true;
            } else {
                echo "❌ Authentication failed\n";
                $this->testResults['authentication'] = false;
            }
        } else {
            echo "❌ No auth token available\n";
            $this->testResults['authentication'] = false;
        }
        echo "\n";
    }

    private function testChatFunctionality()
    {
        echo "💬 Testing Chat Functionality...\n";
        
        // Test getting chats
        $this->testGetChats();
        
        // Test creating a chat (if we have another user)
        $this->testCreateChat();
        
        // Test sending messages
        $this->testSendMessages();
        
        // Test file upload
        $this->testFileUpload();
        
        // Test WebSocket info
        $this->testWebSocketInfo();
    }

    private function testGetChats()
    {
        echo "📋 Testing Get Chats...\n";
        
        $response = $this->makeRequest('GET', '/chats');
        if ($response && isset($response['success']) && $response['success']) {
            $chatCount = count($response['data']['chats'] ?? []);
            echo "✅ Retrieved chats successfully\n";
            echo "   Chat count: {$chatCount}\n";
            $this->testResults['get_chats'] = true;
        } else {
            echo "❌ Failed to get chats: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['get_chats'] = false;
        }
        echo "\n";
    }

    private function testCreateChat()
    {
        echo "➕ Testing Create Chat...\n";
        
        // First, let's try to find another user or create one
        $otherUserData = [
            'name' => 'Chat Partner ' . time(),
            'email' => 'partner' . time() . '@example.com',
            'phone_number' => '+1234567' . substr(time(), -3),
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $userResponse = $this->makeRequest('POST', '/auth/register', $otherUserData);
        
        if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
            $otherUserId = $userResponse['data']['user']['id'];
            
            // Create a private chat
            $chatData = [
                'participants' => [$otherUserId],
                'type' => 'private'
            ];
            
            $response = $this->makeRequest('POST', '/chats', $chatData);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Private chat created successfully\n";
                echo "   Chat ID: " . ($response['data']['id'] ?? 'Unknown') . "\n";
                $this->testResults['create_chat'] = true;
                
                // Store chat ID for message testing
                $this->testChatId = $response['data']['id'];
            } else {
                echo "❌ Failed to create chat: " . ($response['message'] ?? 'Unknown error') . "\n";
                $this->testResults['create_chat'] = false;
            }
        } else {
            echo "⚠️  Could not create second user for chat testing\n";
            $this->testResults['create_chat'] = false;
        }
        echo "\n";
    }

    private function testSendMessages()
    {
        echo "📝 Testing Send Messages...\n";
        
        if (!isset($this->testChatId)) {
            echo "⚠️  No chat available for message testing\n";
            $this->testResults['send_messages'] = false;
            echo "\n";
            return;
        }
        
        $messages = [
            [
                'message_type' => 'text',
                'content' => 'Hello! This is a test message.'
            ],
            [
                'message_type' => 'text',
                'content' => 'Testing emojis: 😊🚀💬'
            ],
            [
                'message_type' => 'text',
                'content' => 'Special characters: @#$%^&*()'
            ]
        ];
        
        $successCount = 0;
        foreach ($messages as $messageData) {
            $response = $this->makeRequest('POST', "/chats/{$this->testChatId}/messages", $messageData);
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ Message sent: " . substr($messageData['content'], 0, 30) . "...\n";
                $successCount++;
            } else {
                echo "❌ Message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
            usleep(500000); // 0.5 second delay
        }
        
        $this->testResults['send_messages'] = $successCount > 0;
        echo "   Messages sent successfully: {$successCount}/" . count($messages) . "\n";
        echo "\n";
    }

    private function testFileUpload()
    {
        echo "📁 Testing File Upload...\n";
        
        // Create a simple test file
        $testContent = "This is a test document for API testing.\nLine 2\nLine 3";
        $testFile = sys_get_temp_dir() . '/test_document_' . time() . '.txt';
        file_put_contents($testFile, $testContent);
        
        try {
            $response = $this->uploadFile($testFile, 'document');
            if ($response && isset($response['success']) && $response['success']) {
                echo "✅ File uploaded successfully\n";
                echo "   File URL: " . ($response['data']['url'] ?? 'N/A') . "\n";
                echo "   File size: " . ($response['data']['size_formatted'] ?? 'N/A') . "\n";
                $this->testResults['file_upload'] = true;
            } else {
                echo "❌ File upload failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                $this->testResults['file_upload'] = false;
            }
        } catch (Exception $e) {
            echo "❌ File upload error: " . $e->getMessage() . "\n";
            $this->testResults['file_upload'] = false;
        }
        
        // Cleanup
        if (file_exists($testFile)) {
            unlink($testFile);
        }
        
        echo "\n";
    }

    private function testWebSocketInfo()
    {
        echo "⚡ Testing WebSocket Info...\n";
        
        $response = $this->makeRequest('GET', '/websocket/connection-info');
        if ($response && isset($response['success']) && $response['success']) {
            echo "✅ WebSocket info retrieved\n";
            echo "   Host: " . ($response['data']['host'] ?? 'N/A') . "\n";
            echo "   Port: " . ($response['data']['port'] ?? 'N/A') . "\n";
            $this->testResults['websocket_info'] = true;
        } else {
            echo "❌ WebSocket info failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['websocket_info'] = false;
        }
        echo "\n";
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

    private function printResults()
    {
        echo "📊 TEST RESULTS SUMMARY\n";
        echo "=======================\n";

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

        if ($successRate >= 80) {
            echo "🎉 Excellent! API is working well.\n";
        } elseif ($successRate >= 60) {
            echo "👍 Good! Most features are working.\n";
        } elseif ($successRate >= 40) {
            echo "⚠️  Fair. Some issues need attention.\n";
        } else {
            echo "🚨 Poor. Significant issues detected.\n";
        }

        echo "\n";
    }
}

// Run the tests
$tester = new SimpleChatApiTester();
$tester->runTests();
