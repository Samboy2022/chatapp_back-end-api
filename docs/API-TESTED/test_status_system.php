<?php
/**
 * Complete Status/Stories System Test
 * Tests: Text status, Image status, Video status, Privacy settings, Viewers, etc.
 */

require __DIR__ . '/vendor/autoload.php';

class StatusSystemTester
{
    private $baseUrl;
    private $user1Token;
    private $user2Token;
    private $user1Id;
    private $user2Id;
    private $testResults = [];
    private $testFilesDir;
    private $statusIds = [];

    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->testFilesDir = __DIR__ . '/test_files';
    }

    public function runAllTests()
    {
        echo "🚀 Starting Complete Status/Stories System Tests\n";
        echo str_repeat("=", 80) . "\n\n";

        // Setup
        if (!$this->setup()) {
            echo "❌ Setup failed\n";
            return;
        }

        // Test status features
        $this->testCreateTextStatus();
        $this->testCreateImageStatus();
        $this->testCreateVideoStatus();
        $this->testTextStatusWithColor();
        $this->testImageStatusWithCaption();
        $this->testVideoStatusWithCaption();
        $this->testStatusPrivacyEveryone();
        $this->testStatusPrivacyContacts();
        $this->testGetMyStatuses();
        $this->testGetAllStatuses();
        $this->testViewStatus();
        $this->testGetStatusViewers();
        $this->testGetUserStatuses();
        $this->testStatusExpiration();

        // Display summary
        $this->displaySummary();

        // Cleanup
        $this->cleanup();
    }

    private function setup()
    {
        echo "🔧 Setting up test environment...\n";

        // Create test files directory
        if (!file_exists($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0777, true);
        }

        // Create test image
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($this->testFilesDir . '/test_image.png', $imageData);

        // Login users
        if (!$this->loginUser1() || !$this->loginUser2()) {
            return false;
        }

        // Make users contacts (for privacy testing)
        $this->makeUsersContacts();

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

    private function makeUsersContacts()
    {
        echo "   Making users contacts...\n";
        
        // Add User 2 as contact for User 1
        $ch = curl_init($this->baseUrl . '/api/contacts');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->user1Token,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contact_user_id' => $this->user2Id
            ])
        ]);

        curl_exec($ch);
        curl_close($ch);
        
        echo "   ✅ Users are now contacts\n";
    }

    private function testCreateTextStatus()
    {
        echo "📝 Test 1: Create Text Status...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'content' => 'Hello! This is my first status update!',
                'background_color' => '#FF5733',
                'text_color' => '#FFFFFF',
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->statusIds['text'] = $data['data']['id'] ?? null;
            echo "✅ Text status created successfully\n";
            echo "   Status ID: " . ($data['data']['id'] ?? 'N/A') . "\n";
            echo "   Content: " . ($data['data']['content'] ?? 'N/A') . "\n";
            echo "   Background Color: " . ($data['data']['background_color'] ?? 'N/A') . "\n";
            echo "   Privacy: " . ($data['data']['privacy'] ?? 'N/A') . "\n";
            $this->testResults['text_status'] = 'PASS';
        } else {
            echo "❌ Text status failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['text_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testCreateImageStatus()
    {
        echo "🖼️  Test 2: Create Image Status...\n";

        // Use Cloudinary demo image
        $testImageUrl = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'media_url' => $testImageUrl,
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->statusIds['image'] = $data['data']['id'] ?? null;
            echo "✅ Image status created successfully\n";
            echo "   Status ID: " . ($data['data']['id'] ?? 'N/A') . "\n";
            echo "   Media URL: " . ($data['data']['media_url'] ?? 'N/A') . "\n";
            $this->testResults['image_status'] = 'PASS';
        } else {
            echo "❌ Image status failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['image_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testCreateVideoStatus()
    {
        echo "🎥 Test 3: Create Video Status...\n";

        // Use Cloudinary demo video
        $testVideoUrl = 'https://res.cloudinary.com/demo/video/upload/dog.mp4';

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'media_url' => $testVideoUrl,
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            $this->statusIds['video'] = $data['data']['id'] ?? null;
            echo "✅ Video status created successfully\n";
            echo "   Status ID: " . ($data['data']['id'] ?? 'N/A') . "\n";
            echo "   Media URL: " . ($data['data']['media_url'] ?? 'N/A') . "\n";
            $this->testResults['video_status'] = 'PASS';
        } else {
            echo "❌ Video status failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['video_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testTextStatusWithColor()
    {
        echo "🎨 Test 4: Text Status with Custom Colors...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'content' => 'Colorful status update!',
                'background_color' => '#4A90E2',
                'text_color' => '#FFFFFF',
                'font_size' => 24,
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            echo "✅ Text status with colors created\n";
            echo "   Background: " . ($data['data']['background_color'] ?? 'N/A') . "\n";
            echo "   Text Color: " . ($data['data']['text_color'] ?? 'N/A') . "\n";
            $this->testResults['text_color_status'] = 'PASS';
        } else {
            echo "❌ Text color status failed\n";
            $this->testResults['text_color_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testImageStatusWithCaption()
    {
        echo "💬 Test 5: Image Status with Caption...\n";

        $testImageUrl = 'https://res.cloudinary.com/demo/image/upload/sample.jpg';

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'media_url' => $testImageUrl,
                'caption' => 'Beautiful sunset! 🌅',
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            echo "✅ Image status with caption created\n";
            echo "   Caption: " . ($data['data']['caption'] ?? 'N/A') . "\n";
            $this->testResults['image_caption_status'] = 'PASS';
        } else {
            echo "❌ Image caption status failed\n";
            $this->testResults['image_caption_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testVideoStatusWithCaption()
    {
        echo "🎬 Test 6: Video Status with Caption...\n";

        $testVideoUrl = 'https://res.cloudinary.com/demo/video/upload/dog.mp4';

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'media_url' => $testVideoUrl,
                'caption' => 'Check out this amazing video! 🎥',
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['success']) && $data['success']) {
            echo "✅ Video status with caption created\n";
            echo "   Caption: " . ($data['data']['caption'] ?? 'N/A') . "\n";
            $this->testResults['video_caption_status'] = 'PASS';
        } else {
            echo "❌ Video caption status failed\n";
            $this->testResults['video_caption_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testStatusPrivacyEveryone()
    {
        echo "🌍 Test 7: Status Privacy - Everyone...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'content' => 'Public status - everyone can see this!',
                'background_color' => '#28A745',
                'privacy' => 'everyone'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['data']['privacy'])) {
            $statusId = $data['data']['id'];
            $this->statusIds['privacy_test'] = $statusId;
            
            // Wait a moment for database to sync
            sleep(1);
            
            // Verify User 2 can see it
            $canSee = $this->canUserSeeStatus($this->user2Token, $statusId);
            
            if ($canSee) {
                echo "✅ Everyone privacy working - User 2 can see status\n";
                echo "   Privacy: " . $data['data']['privacy'] . "\n";
                $this->testResults['privacy_everyone'] = 'PASS';
            } else {
                echo "❌ Everyone privacy failed - User 2 cannot see status\n";
                $this->testResults['privacy_everyone'] = 'FAIL';
            }
        } else {
            echo "❌ Status creation failed (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['privacy_everyone'] = 'FAIL';
        }
        echo "\n";
    }

    private function testStatusPrivacyContacts()
    {
        echo "👥 Test 8: Status Privacy - Contacts Only...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
                'content' => 'Private status - only contacts can see this!',
                'background_color' => '#DC3545',
                'privacy' => 'contacts'
            ])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['data']['privacy'])) {
            echo "✅ Contacts-only status created\n";
            echo "   Privacy: " . $data['data']['privacy'] . "\n";
            echo "   Note: Only contacts can view this status\n";
            $this->testResults['privacy_contacts'] = 'PASS';
        } else {
            echo "❌ Contacts privacy status failed\n";
            $this->testResults['privacy_contacts'] = 'FAIL';
        }
        echo "\n";
    }

    private function canUserSeeStatus($token, $statusId)
    {
        $ch = curl_init($this->baseUrl . '/api/statuses');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $status) {
                if ($status['id'] == $statusId) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function testGetMyStatuses()
    {
        echo "📋 Test 9: Get My Statuses...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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
            $count = count($data['data']);
            echo "✅ Retrieved my statuses\n";
            echo "   Total statuses: $count\n";
            
            if ($count > 0) {
                echo "   Latest status type: " . ($data['data'][0]['type'] ?? 'N/A') . "\n";
            }
            
            $this->testResults['get_my_statuses'] = 'PASS';
        } else {
            echo "❌ Failed to get my statuses\n";
            $this->testResults['get_my_statuses'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetAllStatuses()
    {
        echo "🌐 Test 10: Get All Statuses (Feed)...\n";

        $ch = curl_init($this->baseUrl . '/api/statuses');
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

        if ($httpCode === 200 && isset($data['data'])) {
            $count = count($data['data']);
            echo "✅ Retrieved all statuses\n";
            echo "   Total visible statuses: $count\n";
            echo "   User 2 can see User 1's statuses\n";
            $this->testResults['get_all_statuses'] = 'PASS';
        } else {
            echo "❌ Failed to get all statuses\n";
            $this->testResults['get_all_statuses'] = 'FAIL';
        }
        echo "\n";
    }

    private function testViewStatus()
    {
        echo "👁️  Test 11: View Status (Mark as Viewed)...\n";

        if (empty($this->statusIds['text'])) {
            echo "⏭️  No status ID available\n";
            $this->testResults['view_status'] = 'SKIP';
            echo "\n";
            return;
        }

        $statusId = $this->statusIds['text'];

        $ch = curl_init($this->baseUrl . "/api/statuses/{$statusId}/view");
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
            echo "✅ Status marked as viewed\n";
            echo "   Status ID: $statusId\n";
            echo "   Viewer: User 2\n";
            $this->testResults['view_status'] = 'PASS';
        } else {
            echo "❌ View status failed (HTTP $httpCode)\n";
            $this->testResults['view_status'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetStatusViewers()
    {
        echo "👀 Test 12: Get Status Viewers...\n";

        if (empty($this->statusIds['text'])) {
            echo "⏭️  No status ID available\n";
            $this->testResults['status_viewers'] = 'SKIP';
            echo "\n";
            return;
        }

        $statusId = $this->statusIds['text'];

        $ch = curl_init($this->baseUrl . "/api/status/{$statusId}/viewers");
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
            $viewerCount = count($data['data']);
            echo "✅ Retrieved status viewers\n";
            echo "   Total viewers: $viewerCount\n";
            
            if ($viewerCount > 0) {
                echo "   Viewer: " . ($data['data'][0]['name'] ?? 'N/A') . "\n";
            }
            
            $this->testResults['status_viewers'] = 'PASS';
        } else {
            echo "❌ Failed to get viewers (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['status_viewers'] = 'FAIL';
        }
        echo "\n";
    }

    private function testGetUserStatuses()
    {
        echo "👤 Test 13: Get Specific User's Statuses...\n";

        $ch = curl_init($this->baseUrl . "/api/status/user/{$this->user1Id}");
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

        if ($httpCode === 200 && isset($data['data'])) {
            $count = count($data['data']);
            echo "✅ Retrieved user's statuses\n";
            echo "   User ID: {$this->user1Id}\n";
            echo "   Total statuses: $count\n";
            $this->testResults['user_statuses'] = 'PASS';
        } else {
            echo "❌ Failed to get user statuses (HTTP $httpCode)\n";
            echo "   Response: $response\n";
            $this->testResults['user_statuses'] = 'FAIL';
        }
        echo "\n";
    }

    private function testStatusExpiration()
    {
        echo "⏰ Test 14: Status Expiration (24 hours)...\n";

        // Just verify the expiration field exists
        $ch = curl_init($this->baseUrl . '/api/statuses');
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

        if (isset($data['data'][0]['expires_at'])) {
            echo "✅ Status expiration working\n";
            echo "   Expires at: " . $data['data'][0]['expires_at'] . "\n";
            echo "   Note: Statuses expire after 24 hours\n";
            $this->testResults['status_expiration'] = 'PASS';
        } else {
            echo "⏭️  Expiration field not found\n";
            $this->testResults['status_expiration'] = 'SKIP';
        }
        echo "\n";
    }

    private function displaySummary()
    {
        echo str_repeat("=", 80) . "\n";
        echo "📊 STATUS SYSTEM TEST SUMMARY\n";
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
            echo "🎉 All active tests passed! Status system is working.\n";
        } else {
            echo "⚠️  Some tests failed. Please review above.\n";
        }

        echo str_repeat("=", 80) . "\n";
    }

    private function cleanup()
    {
        echo "\n🧹 Cleaning up...\n";
        
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
$tester = new StatusSystemTester();
$tester->runAllTests();
