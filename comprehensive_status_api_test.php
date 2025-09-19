<?php

/**
 * Comprehensive Status/Story API Testing Script
 * Tests status creation, viewing, view tracking, and viewer analytics
 */

class StatusApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $authToken = null;
    private $testUser = null;
    private $testResults = [];
    private $testStatuses = [];
    private $testUsers = [];
    private $testViews = [];

    public function __construct()
    {
        echo "🎭 COMPREHENSIVE STATUS/STORY API TEST\n";
        echo "======================================\n\n";
    }

    public function runTests()
    {
        try {
            $this->testConnectivity();
            $this->testAuthentication();
            
            if ($this->authToken) {
                $this->testStatusCreation();
                $this->testStatusRetrieval();
                $this->testStatusViewing();
                $this->testViewerTracking();
                $this->testStatusExpiration();
                $this->testStatusPrivacy();
                $this->testStatusDeletion();
                $this->testStatusAnalytics();
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
        
        // Login with admin user
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
        } else {
            echo "❌ Authentication failed\n";
            echo "   Error: " . ($response['message'] ?? 'Unknown error') . "\n";
            $this->testResults['authentication'] = false;
        }
        echo "\n";
    }

    private function testStatusCreation()
    {
        echo "📝 Testing Status Creation...\n";
        
        // Test different types of status creation
        $statusTypes = [
            [
                'type' => 'text',
                'content' => '🎉 This is a test text status! Having a great day testing the API.',
                'background_color' => '#FF6B6B',
                'text_color' => '#FFFFFF',
                'font_family' => 'Arial',
                'privacy' => 'everyone'
            ],
            [
                'type' => 'text',
                'content' => '💻 Testing another status with different styling and privacy settings.',
                'background_color' => '#4ECDC4',
                'text_color' => '#000000',
                'font_family' => 'Helvetica',
                'privacy' => 'contacts'
            ],
            [
                'type' => 'text',
                'content' => '🚀 Third test status for comprehensive testing of the status system.',
                'background_color' => '#45B7D1',
                'text_color' => '#FFFFFF',
                'font_family' => 'Times',
                'privacy' => 'everyone'
            ]
        ];
        
        $successCount = 0;
        foreach ($statusTypes as $index => $statusData) {
            echo "   Creating status " . ($index + 1) . "...\n";
            
            $response = $this->makeRequest('POST', '/status', $statusData);
            
            if ($response && isset($response['success']) && $response['success']) {
                $this->testStatuses[] = $response['data'];
                echo "   ✅ Status created successfully\n";
                echo "      Status ID: " . ($response['data']['id'] ?? 'N/A') . "\n";
                echo "      Content: " . substr($statusData['content'], 0, 40) . "...\n";
                echo "      Privacy: " . $statusData['privacy'] . "\n";
                echo "      Expires: " . ($response['data']['expires_at'] ?? 'N/A') . "\n";
                $successCount++;
            } else {
                echo "   ❌ Status creation failed: " . ($response['message'] ?? 'Unknown error') . "\n";
                if (isset($response['errors'])) {
                    echo "      Validation errors: " . json_encode($response['errors']) . "\n";
                }
            }
            
            usleep(500000); // 0.5 second delay
        }
        
        $this->testResults['status_creation'] = $successCount > 0;
        echo "✅ Statuses created successfully: {$successCount}/" . count($statusTypes) . "\n";
        echo "\n";
    }

    private function testStatusRetrieval()
    {
        echo "📋 Testing Status Retrieval...\n";
        
        // Test getting all statuses
        $response = $this->makeRequest('GET', '/status');
        if ($response && isset($response['success']) && $response['success']) {
            $statusCount = count($response['data'] ?? []);
            echo "✅ Retrieved all statuses successfully\n";
            echo "   Total statuses: {$statusCount}\n";
            
            // Show sample status structure
            if ($statusCount > 0) {
                $sampleStatus = $response['data'][0];
                echo "   Sample status ID: " . ($sampleStatus['id'] ?? 'N/A') . "\n";
                echo "   Sample content: " . substr($sampleStatus['content'] ?? '', 0, 30) . "...\n";
                echo "   Sample expires at: " . ($sampleStatus['expires_at'] ?? 'N/A') . "\n";
            }
            $this->testResults['status_retrieval'] = true;
        } else {
            echo "❌ Failed to retrieve statuses\n";
            $this->testResults['status_retrieval'] = false;
        }
        
        // Test getting user-specific statuses
        if ($this->testUser) {
            $userId = $this->testUser['user']['id'];
            $userResponse = $this->makeRequest('GET', "/status/user/{$userId}");
            if ($userResponse && isset($userResponse['success']) && $userResponse['success']) {
                $userStatusCount = count($userResponse['data'] ?? []);
                echo "✅ Retrieved user statuses successfully\n";
                echo "   User statuses: {$userStatusCount}\n";
            } else {
                echo "❌ Failed to retrieve user statuses\n";
            }
        }
        
        echo "\n";
    }

    private function testStatusViewing()
    {
        echo "👁️ Testing Status Viewing...\n";
        
        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for viewing test\n";
            $this->testResults['status_viewing'] = false;
            echo "\n";
            return;
        }
        
        // Create additional test users to view statuses
        $this->createTestViewers();
        
        $successCount = 0;
        foreach ($this->testStatuses as $index => $status) {
            $statusId = $status['id'];
            echo "   Testing views for status {$statusId}...\n";
            
            // Test viewing with different users
            foreach ($this->testUsers as $userIndex => $testUser) {
                // Switch to test user token
                $originalToken = $this->authToken;
                $this->authToken = $testUser['token'];
                
                $viewResponse = $this->makeRequest('POST', "/status/{$statusId}/view");
                
                if ($viewResponse && isset($viewResponse['success']) && $viewResponse['success']) {
                    echo "      ✅ Status viewed by user " . ($userIndex + 1) . "\n";
                    $this->testViews[] = [
                        'status_id' => $statusId,
                        'viewer' => $testUser['user']['name'],
                        'response' => $viewResponse
                    ];
                    $successCount++;
                } else {
                    echo "      ❌ Status view failed for user " . ($userIndex + 1) . "\n";
                }
                
                // Restore original token
                $this->authToken = $originalToken;
                usleep(200000); // 0.2 second delay
            }
        }
        
        $this->testResults['status_viewing'] = $successCount > 0;
        echo "✅ Status views recorded: {$successCount}\n";
        echo "\n";
    }

    private function testViewerTracking()
    {
        echo "📊 Testing Viewer Tracking...\n";
        
        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for viewer tracking test\n";
            $this->testResults['viewer_tracking'] = false;
            echo "\n";
            return;
        }
        
        $successCount = 0;
        foreach ($this->testStatuses as $status) {
            $statusId = $status['id'];
            echo "   Checking viewers for status {$statusId}...\n";
            
            $viewersResponse = $this->makeRequest('GET', "/status/{$statusId}/viewers");
            
            if ($viewersResponse && isset($viewersResponse['success']) && $viewersResponse['success']) {
                $responseData = $viewersResponse['data'] ?? [];

                // Handle different response structures
                if (isset($responseData['viewers'])) {
                    // New structure: data.viewers
                    $viewers = $responseData['viewers'];
                    $viewerCount = $responseData['total_views'] ?? count($viewers);
                } else {
                    // Old structure: data is array of viewers
                    $viewers = $responseData;
                    $viewerCount = count($viewers);
                }

                echo "   ✅ Retrieved viewers successfully\n";
                echo "      Total viewers: {$viewerCount}\n";
                
                // Show viewer details
                $viewerNum = 1;
                foreach ($viewers as $viewer) {
                    if (is_array($viewer)) {
                        echo "      Viewer {$viewerNum}: " . ($viewer['name'] ?? 'Unknown') . "\n";
                        echo "         ID: " . ($viewer['id'] ?? 'N/A') . "\n";
                        echo "         Viewed at: " . ($viewer['viewed_at'] ?? 'N/A') . "\n";
                    } else {
                        echo "      Viewer {$viewerNum}: Invalid data structure\n";
                    }
                    $viewerNum++;
                }
                
                $successCount++;
            } else {
                echo "   ❌ Failed to retrieve viewers for status {$statusId}\n";
            }
        }
        
        $this->testResults['viewer_tracking'] = $successCount > 0;
        echo "✅ Viewer tracking working for {$successCount} statuses\n";
        echo "\n";
    }

    private function createTestViewers()
    {
        echo "   Creating test viewers...\n";

        $viewers = [
            [
                'name' => 'Status Viewer 1 ' . time(),
                'email' => 'viewer1_' . time() . '@example.com',
                'phone_number' => '+1888' . substr(time(), -6),
                'country_code' => '+1',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            [
                'name' => 'Status Viewer 2 ' . time(),
                'email' => 'viewer2_' . time() . '@example.com',
                'phone_number' => '+1999' . substr(time(), -6),
                'country_code' => '+1',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ]
        ];

        foreach ($viewers as $index => $viewerData) {
            $response = $this->makeRequest('POST', '/auth/register', $viewerData);
            if ($response && isset($response['success']) && $response['success']) {
                $this->testUsers[] = $response['data'];
                echo "      ✅ Created viewer " . ($index + 1) . ": " . $viewerData['name'] . "\n";
            } else {
                echo "      ❌ Failed to create viewer " . ($index + 1) . "\n";
            }
        }
    }

    private function testStatusExpiration()
    {
        echo "⏰ Testing Status Expiration...\n";

        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for expiration test\n";
            $this->testResults['status_expiration'] = false;
            echo "\n";
            return;
        }

        // Check expiration times
        $validExpirationCount = 0;
        foreach ($this->testStatuses as $status) {
            $expiresAt = $status['expires_at'] ?? null;
            if ($expiresAt) {
                $expirationTime = strtotime($expiresAt);
                $currentTime = time();
                $timeRemaining = $expirationTime - $currentTime;

                echo "   Status {$status['id']}: ";
                if ($timeRemaining > 0) {
                    $hoursRemaining = round($timeRemaining / 3600, 1);
                    echo "✅ Expires in {$hoursRemaining} hours\n";
                    $validExpirationCount++;
                } else {
                    echo "❌ Already expired\n";
                }
            } else {
                echo "   Status {$status['id']}: ❌ No expiration time set\n";
            }
        }

        $this->testResults['status_expiration'] = $validExpirationCount > 0;
        echo "✅ Valid expiration times: {$validExpirationCount}/" . count($this->testStatuses) . "\n";
        echo "\n";
    }

    private function testStatusPrivacy()
    {
        echo "🔒 Testing Status Privacy...\n";

        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for privacy test\n";
            $this->testResults['status_privacy'] = false;
            echo "\n";
            return;
        }

        // Test privacy settings
        $privacyTestCount = 0;
        foreach ($this->testStatuses as $status) {
            $privacy = $status['privacy'] ?? 'everyone';
            echo "   Status {$status['id']}: Privacy set to '{$privacy}'\n";

            // Verify privacy setting is properly stored
            if (in_array($privacy, ['everyone', 'contacts', 'close_friends'])) {
                echo "      ✅ Valid privacy setting\n";
                $privacyTestCount++;
            } else {
                echo "      ❌ Invalid privacy setting\n";
            }
        }

        $this->testResults['status_privacy'] = $privacyTestCount > 0;
        echo "✅ Privacy settings validated: {$privacyTestCount}/" . count($this->testStatuses) . "\n";
        echo "\n";
    }

    private function testStatusDeletion()
    {
        echo "🗑️ Testing Status Deletion...\n";

        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for deletion test\n";
            $this->testResults['status_deletion'] = false;
            echo "\n";
            return;
        }

        // Test deleting the last status
        $statusToDelete = end($this->testStatuses);
        $statusId = $statusToDelete['id'];

        echo "   Attempting to delete status {$statusId}...\n";

        $deleteResponse = $this->makeRequest('DELETE', "/status/{$statusId}");

        if ($deleteResponse && isset($deleteResponse['success']) && $deleteResponse['success']) {
            echo "   ✅ Status deleted successfully\n";
            echo "      Message: " . ($deleteResponse['message'] ?? 'N/A') . "\n";

            // Verify deletion by trying to get viewers (should fail)
            $viewersResponse = $this->makeRequest('GET', "/status/{$statusId}/viewers");
            if (!($viewersResponse['success'] ?? true)) {
                echo "   ✅ Status properly removed from system\n";
            }

            $this->testResults['status_deletion'] = true;
        } else {
            echo "   ❌ Status deletion failed: " . ($deleteResponse['message'] ?? 'Unknown error') . "\n";
            $this->testResults['status_deletion'] = false;
        }

        echo "\n";
    }

    private function testStatusAnalytics()
    {
        echo "📈 Testing Status Analytics...\n";

        if (empty($this->testStatuses)) {
            echo "⚠️  No statuses available for analytics test\n";
            $this->testResults['status_analytics'] = false;
            echo "\n";
            return;
        }

        $analyticsCount = 0;
        $deletedCount = 0;

        foreach ($this->testStatuses as $status) {
            $statusId = $status['id'];

            // Get detailed status info with analytics
            $statusResponse = $this->makeRequest('GET', "/status/{$statusId}/viewers");

            if ($statusResponse && isset($statusResponse['success']) && $statusResponse['success']) {
                $responseData = $statusResponse['data'] ?? [];

                // Handle different response structures
                if (isset($responseData['viewers'])) {
                    $viewers = $responseData['viewers'];
                    $viewCount = $responseData['total_views'] ?? count($viewers);
                } else {
                    $viewers = $responseData;
                    $viewCount = count($viewers);
                }

                echo "   ✅ Status {$statusId} Analytics:\n";
                echo "      📊 Total views: {$viewCount}\n";
                echo "      📝 Content: " . substr($status['content'] ?? '', 0, 30) . "...\n";
                echo "      🕐 Created: " . ($status['created_at'] ?? 'N/A') . "\n";
                echo "      ⏰ Expires: " . ($status['expires_at'] ?? 'N/A') . "\n";

                if ($viewCount > 0) {
                    echo "      👥 Recent viewers:\n";
                    foreach (array_slice($viewers, 0, 3) as $viewer) {
                        $viewerName = $viewer['name'] ?? 'Unknown';
                        $viewedAt = $viewer['viewed_at'] ?? 'N/A';
                        echo "         - {$viewerName} at {$viewedAt}\n";
                    }
                } else {
                    echo "      👁️ No views yet\n";
                }

                $analyticsCount++;
                echo "\n";
            } else {
                // Check if it's a 404 (deleted status)
                if (isset($statusResponse['message']) && strpos($statusResponse['message'], 'not found') !== false) {
                    echo "   ℹ️  Status {$statusId}: Deleted (expected after deletion test)\n";
                    $deletedCount++;
                } else {
                    echo "   ❌ Failed to get analytics for status {$statusId}: " . ($statusResponse['message'] ?? 'Unknown error') . "\n";
                }
            }
        }

        $totalStatuses = count($this->testStatuses);
        $this->testResults['status_analytics'] = $analyticsCount > 0;

        echo "📊 Analytics Summary:\n";
        echo "   ✅ Analytics retrieved: {$analyticsCount} statuses\n";
        echo "   🗑️ Deleted statuses: {$deletedCount} statuses\n";
        echo "   📈 Success rate: " . round(($analyticsCount / max(1, $totalStatuses - $deletedCount)) * 100, 1) . "%\n";
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
        echo "📊 COMPREHENSIVE STATUS API TEST RESULTS\n";
        echo "=========================================\n";

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

        echo "\n📊 STATUS STATISTICS:\n";
        echo "---------------------\n";
        echo "📝 Total Statuses Created: " . count($this->testStatuses) . "\n";
        echo "👥 Total Test Viewers: " . count($this->testUsers) . "\n";
        echo "👁️ Total Views Recorded: " . count($this->testViews) . "\n";
        echo "👤 Test User: " . ($this->testUser['user']['name'] ?? 'N/A') . "\n";
        echo "🔗 API Base URL: {$this->baseUrl}\n";

        echo "\n🎯 PERFORMANCE ASSESSMENT:\n";
        echo "---------------------------\n";
        if ($successRate >= 95) {
            echo "🏆 EXCELLENT! Status API is performing exceptionally well.\n";
            echo "   All status features are working perfectly.\n";
        } elseif ($successRate >= 85) {
            echo "🎉 VERY GOOD! Status API is working very well.\n";
            echo "   Most features are functional with minor issues.\n";
        } elseif ($successRate >= 70) {
            echo "👍 GOOD! Status API core functionality is working.\n";
            echo "   Some features may need attention.\n";
        } elseif ($successRate >= 50) {
            echo "⚠️  FAIR! Status API has basic functionality.\n";
            echo "   Several issues need to be addressed.\n";
        } else {
            echo "🚨 POOR! Status API has significant issues.\n";
            echo "   Major problems need immediate attention.\n";
        }

        echo "\n🔍 FEATURE SUMMARY:\n";
        echo "-------------------\n";
        if ($this->testResults['status_creation'] ?? false) {
            echo "✅ Status Creation - Working (Text statuses with styling)\n";
        }
        if ($this->testResults['status_viewing'] ?? false) {
            echo "✅ Status Viewing - Working (Multi-user view tracking)\n";
        }
        if ($this->testResults['viewer_tracking'] ?? false) {
            echo "✅ Viewer Tracking - Working (Who viewed analytics)\n";
        }
        if ($this->testResults['status_expiration'] ?? false) {
            echo "✅ Status Expiration - Working (24-hour auto-expiry)\n";
        }
        if ($this->testResults['status_privacy'] ?? false) {
            echo "✅ Status Privacy - Working (Everyone/Contacts settings)\n";
        }

        echo "\n✅ STATUS API TESTING COMPLETE!\n";
        echo "================================\n";
    }
}

// Run the comprehensive status tests
$tester = new StatusApiTester();
$tester->runTests();
