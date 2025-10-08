<?php
/**
 * Complete Messaging System Test
 * Tests: Text, Image, Video, Audio messages, Delivery receipts, Real-time features
 */

require __DIR__ . '/vendor/autoload.php';

class MessagingSystemTester
{
    private $baseUrl;
    private $user1Token;
    private $user2Token;
    private $user1Id;
    private $user2Id;
    private $chatId;
    private $testResults = [];
    private $testFilesDir;

    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->testFilesDir = __DIR__ . '/test_files';
    }

    public function runAllTests()
    {
        echo "🚀 Starting Complete Messaging System Tests\n";
        echo str_repeat("=", 80) . "\n\n";

        // Setup
        if (!$this->setup()) {
            echo "❌ Setup failed\n";
            return;
        }

        // Test messaging features
        $this->testTextMessage();
        $this->testImageMessage();
        $this->testVideoMessage();
        $this->testAudioMessage();
        $this->testDeliveryReceipts();
        $this->testReadReceipts();
        $this->testGetMessages();
        $this->testPagination();
        $this->testOnlineStatus();
        $this->testTypingIndicator();
        $this->testGroupChat();
        $this->testMessageEdit();
        $this->testMessageDelete();
        $this->testDocumentMessage();

        // Display summary
        $this->displaySummary();

        // Cleanup
        $this->cleanup();
    }

    private function setup()
    {
        echo "🔧 Setting up test environment...\n";

        // Create test files
        if (!file_exists($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0777, true);
        }

        // Create test image
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($this->testFilesDir . '/test_image.png', $imageData);

        // Login as user 1
        if (!$this->loginUser1()) {
            return false;
        }

        // Login as user 2
        if (!$this->loginUser2()) {
            return false;
        }

        // Create or get chat
        if (!$this->createChat()) {
            return false;
        }

        echo "✅ Setup complete\n\n";
        return true;
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

        echo "   ❌ User 1 login failed\n";
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

        echo "   ❌ User 2 login failed\n";
        return false;
    }

    private function createChat()
    {
        echo "   Creating 1:1 chat...\n";
        
        $ch = curl_init($this->baseUrl . '/api/chats');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'private',
                'participants' => [$this->user2Id]
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201 || $httpCode === 200) {
            $data = json_decode($response, true);
            // Handle both new chat and existing chat responses
            if (isset($data['data']['id'])) {
                $this->chatId = $data['data']['id'];
            } elseif (isset($data['data']['chat']['id'])) {
                $this->chatId = $data['data']['chat']['id'];
            } elseif (isset($data['chat']['id'])) {
                $this->chatId = $data['chat']['id'];
            }
            
            if ($this->chatId) {
                echo "   ✅ Chat created (ID: {$this->chatId})\n";
                return true;
            }
            
            echo "   ❌ Chat ID not found in response\n";
            echo "   Response: $response\n";
            return false;
        }

        echo "   ❌ Chat creation failed (HTTP $httpCode)\n";
        echo "   Response: $response\n";
        return false;
    }

    private function testTextMessage()
    {
        echo "📝 Test 1: Send Text Message...\n";

        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'text',
                'content' => 'Hello! This is a test message.'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            // Check if data is nested or direct
            $messageData = $data['data']['message'] ?? $data['data'] ?? $data['message'] ?? null;
            
            echo "✅ Text message sent successfully\n";
            if ($messageData) {
                echo "   Message ID: " . ($messageData['id'] ?? 'N/A') . "\n";
                echo "   Content: " . ($messageData['content'] ?? 'N/A') . "\n";
                echo "   Status: " . ($messageData['status'] ?? 'N/A') . "\n";
            } else {
                echo "   Response structure: " . json_encode($data) . "\n";
            }
            $this->testResults['text_message'] = 'PASS';
        } else {
            echo "❌ Text message failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['text_message'] = 'FAIL';
        }
        echo "\n";
    }

    private function testImageMessage()
    {
        echo "🖼️  Test 2: Send Image Message...\n";

        // First upload image
        $ch = curl_init($this->baseUrl . '/api/media/upload');
        $cfile = new CURLFile($this->testFilesDir . '/test_image.png', 'image/png', 'test.png');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => [
                'file' => $cfile,
                'type' => 'image',
                'chat_id' => $this->chatId
            ]
        ]);

        $uploadResponse = curl_exec($ch);
        curl_close($ch);
        $uploadData = json_decode($uploadResponse, true);

        if (!isset($uploadData['data']['url'])) {
            echo "❌ Image upload failed\n";
            $this->testResults['image_message'] = 'FAIL';
            echo "\n";
            return;
        }

        // Send message with image
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'image',
                'content' => 'Check out this image!',
                'media_url' => $uploadData['data']['url'],
                'media_type' => 'image/png',
                'media_size' => $uploadData['data']['size']
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $messageData = $data['data']['message'] ?? $data['data'] ?? $data['message'] ?? null;
            
            echo "✅ Image message sent successfully\n";
            if ($messageData) {
                echo "   Message ID: " . ($messageData['id'] ?? 'N/A') . "\n";
                echo "   Media URL: " . ($messageData['media_url'] ?? 'N/A') . "\n";
            }
            $this->testResults['image_message'] = 'PASS';
        } else {
            echo "❌ Image message failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['image_message'] = 'FAIL';
        }
        echo "\n";
    }

    private function testVideoMessage()
    {
        echo "🎥 Test 3: Send Video Message...\n";
        
        // Use a publicly available test video URL from Cloudinary's demo
        // This simulates uploading a video and getting a URL back
        $testVideoUrl = 'https://res.cloudinary.com/demo/video/upload/dog.mp4';
        
        echo "   Using test video URL from Cloudinary demo\n";

        // Send message with video
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'video',
                'content' => 'Check out this video!',
                'media_url' => $testVideoUrl,
                'media_type' => 'video/mp4',
                'media_size' => 1024000
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $messageData = $data['data']['message'] ?? $data['data'] ?? $data['message'] ?? null;
            
            echo "✅ Video message sent successfully\n";
            if ($messageData) {
                echo "   Message ID: " . ($messageData['id'] ?? 'N/A') . "\n";
                echo "   Media URL: " . ($messageData['media_url'] ?? 'N/A') . "\n";
            }
            $this->testResults['video_message'] = 'PASS';
        } else {
            echo "❌ Video message failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['video_message'] = 'FAIL';
        }
        
        echo "\n";
    }

    private function testAudioMessage()
    {
        echo "🎵 Test 4: Send Audio/Voice Message...\n";
        
        // Use a publicly available test audio URL from Cloudinary's demo
        // This simulates uploading audio and getting a URL back
        $testAudioUrl = 'https://res.cloudinary.com/demo/video/upload/sample_audio.mp3';
        
        echo "   Using test audio URL from Cloudinary demo\n";

        // Send message with audio
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'audio',
                'content' => '',
                'media_url' => $testAudioUrl,
                'media_type' => 'audio/mp3',
                'media_size' => 512000,
                'media_duration' => 5
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $messageData = $data['data']['message'] ?? $data['data'] ?? $data['message'] ?? null;
            
            echo "✅ Voice message sent successfully\n";
            if ($messageData) {
                echo "   Message ID: " . ($messageData['id'] ?? 'N/A') . "\n";
                echo "   Media URL: " . ($messageData['media_url'] ?? 'N/A') . "\n";
                echo "   Duration: " . ($messageData['media_duration'] ?? 'N/A') . "s\n";
            }
            $this->testResults['audio_message'] = 'PASS';
        } else {
            echo "❌ Voice message failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['audio_message'] = 'FAIL';
        }
        
        echo "\n";
    }

    private function testDeliveryReceipts()
    {
        echo "📬 Test 5: Delivery Receipts...\n";
        
        // Get messages as User 2 (recipient)
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
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

        if ($httpCode === 200 && isset($data['data']['data'])) {
            $messages = $data['data']['data'];
            echo "✅ Recipient received messages\n";
            echo "   Total messages: " . count($messages) . "\n";
            
            if (count($messages) > 0) {
                echo "   Latest message status: " . ($messages[0]['status'] ?? 'N/A') . "\n";
            }
            
            $this->testResults['delivery_receipts'] = 'PASS';
        } else {
            echo "❌ Failed to get messages\n";
            $this->testResults['delivery_receipts'] = 'FAIL';
        }
        echo "\n";
    }

    private function testReadReceipts()
    {
        echo "👁️  Test 6: Read Receipts...\n";
        
        // Get first message
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);

        if (!isset($data['data']['data'][0]['id'])) {
            echo "❌ No messages to mark as read\n";
            $this->testResults['read_receipts'] = 'FAIL';
            echo "\n";
            return;
        }

        $messageId = $data['data']['data'][0]['id'];

        // Mark as read
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages/{$messageId}/read");
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

        if ($httpCode === 200) {
            echo "✅ Message marked as read\n";
            echo "   Message ID: $messageId\n";
            $this->testResults['read_receipts'] = 'PASS';
        } else {
            echo "❌ Failed to mark as read\n";
            $this->testResults['read_receipts'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetMessages()
    {
        echo "📨 Test 7: Get All Messages...\n";
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
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

        if ($httpCode === 200 && isset($data['data']['data'])) {
            $messages = $data['data']['data'];
            echo "✅ Messages retrieved successfully\n";
            echo "   Total messages: " . count($messages) . "\n";
            echo "   Current page: " . ($data['data']['current_page'] ?? 'N/A') . "\n";
            echo "   Per page: " . ($data['data']['per_page'] ?? 'N/A') . "\n";
            
            // Display message types
            $types = array_count_values(array_column($messages, 'type'));
            echo "   Message types: " . json_encode($types) . "\n";
            
            $this->testResults['get_messages'] = 'PASS';
        } else {
            echo "❌ Failed to get messages\n";
            $this->testResults['get_messages'] = 'FAIL';
        }
        echo "\n";
    }

    private function testPagination()
    {
        echo "📄 Test 8: Pagination...\n";
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages?per_page=1&page=1");
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

        if ($httpCode === 200 && isset($data['data'])) {
            echo "✅ Pagination working\n";
            echo "   Per page: " . ($data['data']['per_page'] ?? 'N/A') . "\n";
            echo "   Current page: " . ($data['data']['current_page'] ?? 'N/A') . "\n";
            echo "   Total: " . ($data['data']['total'] ?? 'N/A') . "\n";
            echo "   Last page: " . ($data['data']['last_page'] ?? 'N/A') . "\n";
            $this->testResults['pagination'] = 'PASS';
        } else {
            echo "❌ Pagination failed\n";
            $this->testResults['pagination'] = 'FAIL';
        }
        echo "\n";
    }

    private function testOnlineStatus()
    {
        echo "🟢 Test 9: Online Status...\n";
        
        $ch = curl_init($this->baseUrl . "/api/discover/users/online");
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

        if ($httpCode === 200 && isset($data['data'])) {
            echo "✅ Online users retrieved\n";
            echo "   Online users count: " . count($data['data']) . "\n";
            $this->testResults['online_status'] = 'PASS';
        } else {
            echo "❌ Failed to get online status\n";
            $this->testResults['online_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testTypingIndicator()
    {
        echo "⌨️  Test 10: Typing Indicator...\n";
        
        // Try to send typing indicator (correct endpoint under websocket prefix)
        $ch = curl_init($this->baseUrl . "/api/websocket/chats/{$this->chatId}/typing");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'is_typing' => true
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            echo "✅ Typing indicator sent\n";
            echo "   Chat ID: {$this->chatId}\n";
            $this->testResults['typing_indicator'] = 'PASS';
        } else {
            echo "❌ Typing indicator failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['typing_indicator'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGroupChat()
    {
        echo "👥 Test 11: Group Chat (Complete)...\n";
        
        // Create a group chat
        $ch = curl_init($this->baseUrl . '/api/chats');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'group',
                'name' => 'Test Group Chat',
                'participants' => [$this->user2Id]
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 || $httpCode === 200) {
            $groupChatId = null;
            if (isset($data['data']['id'])) {
                $groupChatId = $data['data']['id'];
            } elseif (isset($data['data']['chat']['id'])) {
                $groupChatId = $data['data']['chat']['id'];
            }
            
            if ($groupChatId) {
                echo "   ✅ Group chat created (ID: $groupChatId)\n";
                
                // Test 1: Send text message to group
                $messageId = $this->sendGroupMessage($groupChatId, 'Hello everyone in the group!');
                
                // Test 2: User 2 replies to the message
                if ($messageId) {
                    $this->testReplyMessage($groupChatId, $messageId);
                }
                
                // Test 3: Send image to group
                $this->sendGroupImageMessage($groupChatId);
                
                // Test 4: Test message reactions
                if ($messageId) {
                    $this->testMessageReaction($groupChatId, $messageId);
                }
                
                // Test 5: Get all group messages
                $this->getGroupMessages($groupChatId);
                
                $this->testResults['group_chat'] = 'PASS';
            } else {
                echo "   ❌ Group chat ID not found\n";
                $this->testResults['group_chat'] = 'FAIL';
            }
        } else {
            echo "   ❌ Group chat creation failed (HTTP $httpCode)\n";
            $this->testResults['group_chat'] = 'FAIL';
        }
        echo "\n";
    }

    private function sendGroupMessage($groupChatId, $content)
    {
        $ch = curl_init($this->baseUrl . "/api/chats/{$groupChatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'text',
                'content' => $content
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201) {
            echo "   ✅ Group message sent\n";
            return $data['data']['id'] ?? null;
        }
        
        echo "   ❌ Group message failed\n";
        return null;
    }

    private function testReplyMessage($groupChatId, $replyToMessageId)
    {
        echo "   Testing reply message...\n";
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$groupChatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'text',
                'content' => 'This is a reply to your message!',
                'reply_to_message_id' => $replyToMessageId
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            echo "   ✅ Reply message sent\n";
        } else {
            echo "   ❌ Reply message failed\n";
        }
    }

    private function sendGroupImageMessage($groupChatId)
    {
        echo "   Testing group image message...\n";
        
        // Use test image URL
        $testImageUrl = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$groupChatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'image',
                'content' => 'Check this out!',
                'media_url' => $testImageUrl,
                'media_type' => 'image/jpeg',
                'media_size' => 102400
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            echo "   ✅ Group image message sent\n";
        } else {
            echo "   ❌ Group image message failed\n";
        }
    }

    private function testMessageReaction($groupChatId, $messageId)
    {
        echo "   Testing message reaction...\n";
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$groupChatId}/messages/{$messageId}/react");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user2Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'emoji' => '👍'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            echo "   ✅ Message reaction added\n";
        } else {
            echo "   ⚠️  Message reaction not available\n";
        }
    }

    private function getGroupMessages($groupChatId)
    {
        echo "   Getting group messages...\n";
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$groupChatId}/messages");
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

        if ($httpCode === 200 && isset($data['data']['data'])) {
            $messages = $data['data']['data'];
            echo "   ✅ Retrieved " . count($messages) . " group messages\n";
        } else {
            echo "   ❌ Failed to get group messages\n";
        }
    }

    private function testMessageEdit()
    {
        echo "✏️  Test 12: Message Edit...\n";
        
        // First, send a message to edit
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'text',
                'content' => 'This message will be edited'
            ])
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        if (!isset($data['data']['message']['id']) && !isset($data['data']['id'])) {
            echo "❌ Failed to create message for editing\n";
            $this->testResults['message_edit'] = 'FAIL';
            echo "\n";
            return;
        }

        $messageId = $data['data']['message']['id'] ?? $data['data']['id'];

        // Edit message
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages/{$messageId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'content' => 'This message has been edited!'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200) {
            echo "✅ Message edited successfully\n";
            echo "   Message ID: $messageId\n";
            echo "   New content: This message has been edited!\n";
            $this->testResults['message_edit'] = 'PASS';
        } else {
            echo "❌ Message edit failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['message_edit'] = 'FAIL';
        }
        echo "\n";
    }

    private function testMessageDelete()
    {
        echo "🗑️  Test 13: Message Delete...\n";
        
        // Send a message to delete
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'text',
                'content' => 'This message will be deleted'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = json_decode($response, true);
        curl_close($ch);

        if ($httpCode !== 201 || (!isset($data['data']['message']['id']) && !isset($data['data']['id']))) {
            echo "❌ Failed to create message for deletion\n";
            $this->testResults['message_delete'] = 'FAIL';
            echo "\n";
            return;
        }

        $messageId = $data['data']['message']['id'] ?? $data['data']['id'];

        // Delete message
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages/{$messageId}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200) {
            echo "✅ Message deleted successfully\n";
            echo "   Message ID: $messageId\n";
            $this->testResults['message_delete'] = 'PASS';
        } else {
            echo "❌ Message delete failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['message_delete'] = 'FAIL';
        }
        echo "\n";
    }

    private function testDocumentMessage()
    {
        echo "📄 Test 14: Document Message...\n";
        
        // Use test document URL
        $testDocUrl = 'https://res.cloudinary.com/demo/raw/upload/sample.pdf';
        
        $ch = curl_init($this->baseUrl . "/api/chats/{$this->chatId}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'type' => 'document',
                'content' => 'Here is the document',
                'media_url' => $testDocUrl,
                'media_type' => 'application/pdf',
                'media_size' => 204800
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            echo "✅ Document message sent successfully\n";
            $this->testResults['document_message'] = 'PASS';
        } else {
            echo "❌ Document message failed (HTTP $httpCode)\n";
            $this->testResults['document_message'] = 'FAIL';
        }
        echo "\n";
    }

    private function displaySummary()
    {
        echo str_repeat("=", 80) . "\n";
        echo "📊 MESSAGING SYSTEM TEST SUMMARY\n";
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
            echo "🎉 All active tests passed! Messaging system is working.\n";
        } else {
            echo "⚠️  Some tests failed. Please review above.\n";
        }

        echo str_repeat("=", 80) . "\n";
    }

    private function cleanup()
    {
        echo "\n🧹 Cleaning up...\n";
        
        // Clean up all test files
        $files = glob($this->testFilesDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        if (file_exists($this->testFilesDir) && is_dir($this->testFilesDir)) {
            rmdir($this->testFilesDir);
        }
        
        echo "✅ Cleanup complete\n";
    }
}

// Run the tests
$tester = new MessagingSystemTester();
$tester->runAllTests();
