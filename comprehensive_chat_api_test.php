<?php

/**
 * Comprehensive Chat API Testing Script
 * Tests all chat functionality including text messages, file uploads, voice messages, and group chats
 */

class ChatApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $authToken = null;
    private $testUsers = [];
    private $testChats = [];
    private $testMessages = [];

    public function __construct()
    {
        echo "🚀 Starting Comprehensive Chat API Testing\n";
        echo "==========================================\n\n";
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        try {
            // Step 1: Setup test users
            $this->setupTestUsers();
            
            // Step 2: Test authentication
            $this->testAuthentication();
            
            // Step 3: Test private chat creation
            $this->testPrivateChatCreation();
            
            // Step 4: Test text messaging
            $this->testTextMessaging();
            
            // Step 5: Test file uploads
            $this->testFileUploads();
            
            // Step 6: Test voice messages
            $this->testVoiceMessages();
            
            // Step 7: Test group chat creation
            $this->testGroupChatCreation();
            
            // Step 8: Test group messaging
            $this->testGroupMessaging();
            
            // Step 9: Test message reactions
            $this->testMessageReactions();
            
            // Step 10: Test message replies
            $this->testMessageReplies();
            
            // Step 11: Test real-time features
            $this->testRealTimeFeatures();
            
            // Step 12: Test error handling
            $this->testErrorHandling();
            
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Fatal Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Setup test users
     */
    private function setupTestUsers()
    {
        echo "👥 Setting up test users...\n";
        
        $users = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@test.com',
                'phone_number' => '+1234567890',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob@test.com',
                'phone_number' => '+1234567891',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            [
                'name' => 'Charlie Brown',
                'email' => 'charlie@test.com',
                'phone_number' => '+1234567892',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ]
        ];

        foreach ($users as $userData) {
            $response = $this->makeRequest('POST', '/auth/register', $userData);
            if ($response['success']) {
                $this->testUsers[] = $response['data'];
                echo "✅ Created user: {$userData['name']}\n";
            } else {
                echo "⚠️  User {$userData['name']} might already exist\n";
                // Try to login instead
                $loginResponse = $this->makeRequest('POST', '/auth/login', [
                    'login' => $userData['email'],
                    'password' => $userData['password']
                ]);
                if ($loginResponse['success']) {
                    $this->testUsers[] = $loginResponse['data'];
                    echo "✅ Logged in existing user: {$userData['name']}\n";
                }
            }
        }
        
        echo "📊 Total test users created/logged in: " . count($this->testUsers) . "\n\n";
    }

    /**
     * Test authentication
     */
    private function testAuthentication()
    {
        echo "🔐 Testing Authentication...\n";
        
        if (count($this->testUsers) > 0) {
            $this->authToken = $this->testUsers[0]['token'];
            echo "✅ Authentication token set for: " . $this->testUsers[0]['user']['name'] . "\n";
            
            // Test authenticated endpoint
            $response = $this->makeRequest('GET', '/auth/user');
            if ($response['success'] ?? false) {
                echo "✅ Authenticated user endpoint working\n";
            } else {
                echo "❌ Authenticated user endpoint failed\n";
            }
        } else {
            throw new Exception("No test users available for authentication");
        }
        
        echo "\n";
    }

    /**
     * Test private chat creation
     */
    private function testPrivateChatCreation()
    {
        echo "💬 Testing Private Chat Creation...\n";
        
        if (count($this->testUsers) >= 2) {
            $chatData = [
                'participants' => [$this->testUsers[1]['user']['id']],
                'type' => 'private'
            ];
            
            $response = $this->makeRequest('POST', '/chats', $chatData);
            if ($response['success'] ?? false) {
                $this->testChats['private'] = $response['data'];
                echo "✅ Private chat created successfully\n";
                echo "   Chat ID: " . $this->testChats['private']['id'] . "\n";
            } else {
                echo "❌ Private chat creation failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
        }
        
        echo "\n";
    }

    /**
     * Test text messaging
     */
    private function testTextMessaging()
    {
        echo "📝 Testing Text Messaging...\n";
        
        if (isset($this->testChats['private'])) {
            $chatId = $this->testChats['private']['id'];
            
            $messages = [
                "Hello! This is a test message.",
                "How are you doing today?",
                "This is another test message with emojis! 😊🚀",
                "Testing special characters: @#$%^&*()",
                "A longer message to test the system's ability to handle more content. This message contains multiple sentences and should be processed correctly by the messaging system."
            ];
            
            foreach ($messages as $content) {
                $messageData = [
                    'message_type' => 'text',
                    'content' => $content
                ];
                
                $response = $this->makeRequest('POST', "/chats/{$chatId}/messages", $messageData);
                if ($response['success'] ?? false) {
                    $this->testMessages[] = $response['data'];
                    echo "✅ Text message sent: " . substr($content, 0, 50) . "...\n";
                } else {
                    echo "❌ Text message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                }
                
                usleep(500000); // 0.5 second delay between messages
            }
        } else {
            echo "❌ No private chat available for text messaging\n";
        }
        
        echo "\n";
    }

    /**
     * Test file uploads
     */
    private function testFileUploads()
    {
        echo "📁 Testing File Uploads...\n";
        
        // Create test files
        $testFiles = $this->createTestFiles();
        
        foreach ($testFiles as $fileType => $filePath) {
            echo "📤 Testing {$fileType} upload...\n";
            
            $response = $this->uploadFile($filePath, $fileType);
            if ($response['success'] ?? false) {
                echo "✅ {$fileType} upload successful\n";
                echo "   File URL: " . $response['data']['url'] . "\n";
                
                // Send as message if we have a chat
                if (isset($this->testChats['private'])) {
                    $this->sendMediaMessage($this->testChats['private']['id'], $response['data'], $fileType);
                }
            } else {
                echo "❌ {$fileType} upload failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
        }
        
        // Cleanup test files
        $this->cleanupTestFiles($testFiles);
        
        echo "\n";
    }

    /**
     * Test voice messages
     */
    private function testVoiceMessages()
    {
        echo "🎤 Testing Voice Messages...\n";
        
        // Create a fake audio file for testing
        $audioFile = $this->createTestAudioFile();
        
        if ($audioFile) {
            $response = $this->uploadFile($audioFile, 'voice');
            if ($response['success'] ?? false) {
                echo "✅ Voice message upload successful\n";
                echo "   Duration: " . ($response['data']['duration'] ?? 'N/A') . " seconds\n";
                
                // Send as voice message
                if (isset($this->testChats['private'])) {
                    $this->sendMediaMessage($this->testChats['private']['id'], $response['data'], 'voice');
                }
            } else {
                echo "❌ Voice message upload failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
            
            unlink($audioFile);
        }
        
        echo "\n";
    }

    /**
     * Test group chat creation
     */
    private function testGroupChatCreation()
    {
        echo "👥 Testing Group Chat Creation...\n";
        
        if (count($this->testUsers) >= 3) {
            $groupData = [
                'type' => 'group',
                'name' => 'Test Group Chat',
                'description' => 'This is a test group for API testing',
                'participants' => [
                    $this->testUsers[1]['user']['id'],
                    $this->testUsers[2]['user']['id']
                ]
            ];
            
            $response = $this->makeRequest('POST', '/chats', $groupData);
            if ($response['success'] ?? false) {
                $this->testChats['group'] = $response['data'];
                echo "✅ Group chat created successfully\n";
                echo "   Group ID: " . $this->testChats['group']['id'] . "\n";
                echo "   Group Name: " . $this->testChats['group']['name'] . "\n";
            } else {
                echo "❌ Group chat creation failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "❌ Not enough users for group chat creation\n";
        }
        
        echo "\n";
    }

    /**
     * Test group messaging
     */
    private function testGroupMessaging()
    {
        echo "👥💬 Testing Group Messaging...\n";
        
        if (isset($this->testChats['group'])) {
            $chatId = $this->testChats['group']['id'];
            
            $groupMessages = [
                "Hello everyone! Welcome to the test group!",
                "This is a group message test.",
                "Let's see if everyone receives this message.",
                "Group messaging is working great! 🎉"
            ];
            
            foreach ($groupMessages as $content) {
                $messageData = [
                    'message_type' => 'text',
                    'content' => $content
                ];
                
                $response = $this->makeRequest('POST', "/chats/{$chatId}/messages", $messageData);
                if ($response['success'] ?? false) {
                    echo "✅ Group message sent: " . substr($content, 0, 40) . "...\n";
                } else {
                    echo "❌ Group message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                }
                
                usleep(300000); // 0.3 second delay
            }
        } else {
            echo "❌ No group chat available for messaging\n";
        }
        
        echo "\n";
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($method, $endpoint, $data = null, $isMultipart = false)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        
        $headers = ['Accept: application/json'];
        
        if ($this->authToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }
        
        if ($data) {
            if ($isMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => 'CURL Error: ' . $error];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            return [
                'success' => false,
                'message' => $decodedResponse['message'] ?? 'HTTP Error ' . $httpCode,
                'errors' => $decodedResponse['errors'] ?? null
            ];
        }
        
        return $decodedResponse ?? ['success' => false, 'message' => 'Invalid JSON response'];
    }

    /**
     * Upload file
     */
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
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->authToken,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? ['success' => false, 'message' => 'Upload failed'];
    }

    /**
     * Send media message
     */
    private function sendMediaMessage($chatId, $mediaData, $type)
    {
        $messageData = [
            'message_type' => $type,
            'content' => "Shared a {$type} file",
            'media_url' => $mediaData['url'],
            'media_size' => $mediaData['size'],
            'media_mime_type' => $mediaData['mime_type'],
            'file_name' => $mediaData['original_name']
        ];

        if (isset($mediaData['duration'])) {
            $messageData['media_duration'] = $mediaData['duration'];
        }

        $response = $this->makeRequest('POST', "/chats/{$chatId}/messages", $messageData);
        if ($response['success'] ?? false) {
            echo "✅ Media message sent to chat\n";
        } else {
            echo "❌ Media message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
    }

    /**
     * Create test files
     */
    private function createTestFiles()
    {
        $files = [];

        // Create test image
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jU8j8wAAAABJRU5ErkJggg==');
        $imagePath = sys_get_temp_dir() . '/test_image.png';
        file_put_contents($imagePath, $imageContent);
        $files['image'] = $imagePath;

        // Create test document
        $docContent = "This is a test document for API testing.\nIt contains multiple lines.\nAnd some special characters: @#$%";
        $docPath = sys_get_temp_dir() . '/test_document.txt';
        file_put_contents($docPath, $docContent);
        $files['document'] = $docPath;

        return $files;
    }

    /**
     * Create test audio file
     */
    private function createTestAudioFile()
    {
        // Create a minimal WAV file (silence)
        $audioPath = sys_get_temp_dir() . '/test_voice.wav';

        // WAV header for 1 second of silence at 8kHz, 16-bit, mono
        $wavHeader = pack('A4VVA4A4VVVVVVA4V',
            'RIFF', 36, 8, 'WAVE', 'fmt ', 16, 1, 8000, 16000, 2, 16, 'data', 0);

        file_put_contents($audioPath, $wavHeader);

        return $audioPath;
    }

    /**
     * Cleanup test files
     */
    private function cleanupTestFiles($files)
    {
        foreach ($files as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Test message reactions
     */
    private function testMessageReactions()
    {
        echo "😊 Testing Message Reactions...\n";

        if (!empty($this->testMessages)) {
            $messageId = $this->testMessages[0]['id'];

            $reactions = ['👍', '❤️', '😂', '😮', '😢', '🔥'];

            foreach ($reactions as $reaction) {
                $response = $this->makeRequest('POST', "/messages/{$messageId}/react", [
                    'reaction' => $reaction
                ]);

                if ($response['success'] ?? false) {
                    echo "✅ Reaction added: {$reaction}\n";
                } else {
                    echo "❌ Reaction failed: {$reaction} - " . ($response['message'] ?? 'Unknown error') . "\n";
                }
            }
        } else {
            echo "❌ No messages available for reactions\n";
        }

        echo "\n";
    }

    /**
     * Test message replies
     */
    private function testMessageReplies()
    {
        echo "↩️ Testing Message Replies...\n";

        if (!empty($this->testMessages) && isset($this->testChats['private'])) {
            $originalMessage = $this->testMessages[0];
            $chatId = $this->testChats['private']['id'];

            $replyData = [
                'message_type' => 'text',
                'content' => 'This is a reply to your message!',
                'reply_to_message_id' => $originalMessage['id']
            ];

            $response = $this->makeRequest('POST', "/chats/{$chatId}/messages", $replyData);
            if ($response['success'] ?? false) {
                echo "✅ Reply message sent successfully\n";
                echo "   Replying to: " . substr($originalMessage['content'], 0, 30) . "...\n";
            } else {
                echo "❌ Reply message failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "❌ No messages or chats available for replies\n";
        }

        echo "\n";
    }

    /**
     * Test real-time features
     */
    private function testRealTimeFeatures()
    {
        echo "⚡ Testing Real-time Features...\n";

        // Test WebSocket connection info
        $response = $this->makeRequest('GET', '/websocket/connection-info');
        if ($response['success'] ?? false) {
            echo "✅ WebSocket connection info retrieved\n";
            echo "   Host: " . ($response['data']['host'] ?? 'N/A') . "\n";
            echo "   Port: " . ($response['data']['port'] ?? 'N/A') . "\n";
        } else {
            echo "❌ WebSocket connection info failed\n";
        }

        // Test online status update
        $response = $this->makeRequest('POST', '/websocket/online-status', [
            'is_online' => true
        ]);
        if ($response['success'] ?? false) {
            echo "✅ Online status updated\n";
        } else {
            echo "❌ Online status update failed\n";
        }

        // Test typing indicator
        if (isset($this->testChats['private'])) {
            $chatId = $this->testChats['private']['id'];
            $response = $this->makeRequest('POST', "/websocket/chats/{$chatId}/typing", [
                'is_typing' => true
            ]);
            if ($response['success'] ?? false) {
                echo "✅ Typing indicator sent\n";
            } else {
                echo "❌ Typing indicator failed\n";
            }
        }

        echo "\n";
    }

    /**
     * Test error handling
     */
    private function testErrorHandling()
    {
        echo "🚨 Testing Error Handling...\n";

        // Test invalid chat access
        $response = $this->makeRequest('GET', '/chats/99999/messages');
        if (!($response['success'] ?? true)) {
            echo "✅ Invalid chat access properly blocked\n";
        } else {
            echo "❌ Invalid chat access not properly handled\n";
        }

        // Test invalid message data
        if (isset($this->testChats['private'])) {
            $chatId = $this->testChats['private']['id'];
            $response = $this->makeRequest('POST', "/chats/{$chatId}/messages", [
                'message_type' => 'invalid_type',
                'content' => ''
            ]);
            if (!($response['success'] ?? true)) {
                echo "✅ Invalid message data properly validated\n";
            } else {
                echo "❌ Invalid message data not properly handled\n";
            }
        }

        // Test unauthorized access (without token)
        $originalToken = $this->authToken;
        $this->authToken = null;
        $response = $this->makeRequest('GET', '/chats');
        if (!($response['success'] ?? true)) {
            echo "✅ Unauthorized access properly blocked\n";
        } else {
            echo "❌ Unauthorized access not properly handled\n";
        }
        $this->authToken = $originalToken;

        echo "\n";
    }

    /**
     * Print test summary
     */
    private function printSummary()
    {
        echo "📊 TEST SUMMARY\n";
        echo "===============\n";
        echo "✅ Users created: " . count($this->testUsers) . "\n";
        echo "✅ Chats created: " . count($this->testChats) . "\n";
        echo "✅ Messages sent: " . count($this->testMessages) . "\n";
        echo "\n";
        echo "🎉 Comprehensive Chat API Testing Complete!\n";
        echo "Check the output above for detailed results.\n";
    }
}

// Run the tests
$tester = new ChatApiTester();
$tester->runAllTests();
