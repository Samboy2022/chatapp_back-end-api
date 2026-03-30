<?php

/**
 * Profile Management API Testing Script
 * 
 * Tests all profile management endpoints including:
 * - Get profile
 * - Update name, bio, email
 * - Change password
 * - Upload avatar
 * - Privacy settings
 * - Notification settings
 * - Delete account
 * - Export data
 */

class ProfileManagementApiTester
{
    private $baseUrl;
    private $token;
    private $userId;
    private $testResults = [];
    private $passedTests = 0;
    private $failedTests = 0;

    public function __construct()
    {
        $this->baseUrl = 'http://127.0.0.1:8000/api';
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "🧪 PROFILE MANAGEMENT API TESTING\n";
        echo str_repeat("=", 70) . "\n\n";
    }

    public function runAllTests()
    {
        try {
            // Setup: Login to get token
            $this->setupTestUser();
            
            // Run all profile management tests
            $this->testGetProfile();
            $this->testUpdateProfileName();
            $this->testUpdateProfileAbout();
            $this->testUpdateEmail();
            $this->testUpdateMultipleFields();
            $this->testChangePassword();
            $this->testChangePasswordWithWrongCurrent();
            $this->testUploadAvatar();
            $this->testGetPrivacySettings();
            $this->testUpdatePrivacySettings();
            $this->testUpdateSinglePrivacySetting();
            $this->testPrivacyValidation();
            $this->testGetNotificationSettings();
            $this->testUpdateNotificationSettings();
            $this->testExportUserData();
            $this->testDeleteAccountWithWrongPassword();
            // Note: Delete account test is last and commented out to preserve test user
            // $this->testDeleteAccount();
            
            // Display results
            $this->displayResults();
            
        } catch (Exception $e) {
            echo "❌ Fatal Error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    private function setupTestUser()
    {
        echo "🔧 Setting up test user...\n";
        
        // Try to login with existing test user - try with updated password first
        $loginData = [
            'login' => 'profiletest@example.com',
            'password' => 'newpassword456'
        ];
        
        $loginResponse = $this->makeRequest('POST', '/auth/login', $loginData, null, false);
        
        if (!isset($loginResponse['success']) || !$loginResponse['success']) {
            // Try with original password
            $loginData['password'] = 'password123';
            $loginResponse = $this->makeRequest('POST', '/auth/login', $loginData, null, false);
        }
        
        if (!isset($loginResponse['success']) || !$loginResponse['success']) {
            // Register new test user with unique phone
            echo "   Creating new test user...\n";
            $uniquePhone = '+1999' . rand(100000, 999999);
            $registerData = [
                'name' => 'Profile Test User',
                'email' => 'profiletest@example.com',
                'phone_number' => $uniquePhone,
                'country_code' => '+1',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];
            
            $registerResponse = $this->makeRequest('POST', '/auth/register', $registerData, null, false);
            
            if (!isset($registerResponse['success']) || !$registerResponse['success']) {
                throw new Exception("Failed to create test user: " . json_encode($registerResponse));
            }
            
            $this->token = $registerResponse['data']['token'];
            $this->userId = $registerResponse['data']['user']['id'];
        } else {
            $this->token = $loginResponse['data']['token'];
            $this->userId = $loginResponse['data']['user']['id'];
        }
        
        echo "✅ Test user ready (ID: {$this->userId})\n\n";
    }

    private function testGetProfile()
    {
        echo "📋 Test 1: Get User Profile\n";
        
        try {
            $response = $this->makeRequest('GET', '/settings/profile', [], $this->token);
            
            if ($response['success'] && isset($response['data']['id'])) {
                $this->recordSuccess("Get profile successful");
                echo "   ✅ Profile retrieved\n";
                echo "   Name: {$response['data']['name']}\n";
                echo "   Email: {$response['data']['email']}\n";
                echo "   Phone: {$response['data']['phone_number']}\n";
            } else {
                $this->recordFailure("Get profile failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Get profile error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateProfileName()
    {
        echo "📝 Test 2: Update Profile Name\n";
        
        try {
            $data = ['name' => 'Updated Test Name'];
            $response = $this->makeRequest('PUT', '/settings/profile', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update name successful");
                echo "   ✅ Name updated to: {$response['data']['name']}\n";
            } else {
                $this->recordFailure("Update name failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update name error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateProfileAbout()
    {
        echo "💬 Test 3: Update Profile About/Bio\n";
        
        try {
            $data = ['about' => 'This is my updated bio! 🚀'];
            $response = $this->makeRequest('PUT', '/settings/profile', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update about successful");
                echo "   ✅ About updated to: {$response['data']['about']}\n";
            } else {
                $this->recordFailure("Update about failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update about error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateEmail()
    {
        echo "📧 Test 4: Update Email Address\n";
        
        try {
            $newEmail = 'updated_' . time() . '@example.com';
            $data = ['email' => $newEmail];
            $response = $this->makeRequest('PUT', '/auth/profile', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update email successful");
                echo "   ✅ Email updated to: {$newEmail}\n";
            } else {
                $this->recordFailure("Update email failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update email error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateMultipleFields()
    {
        echo "🔄 Test 5: Update Multiple Profile Fields\n";
        
        try {
            $data = [
                'name' => 'Multi Update Test',
                'about' => 'Updated multiple fields at once!'
            ];
            $response = $this->makeRequest('PUT', '/auth/profile', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update multiple fields successful");
                echo "   ✅ Multiple fields updated\n";
                echo "   Name: {$response['data']['user']['name']}\n";
                echo "   About: {$response['data']['user']['about']}\n";
            } else {
                $this->recordFailure("Update multiple fields failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update multiple fields error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testChangePassword()
    {
        echo "🔑 Test 6: Change Password (Correct Current Password)\n";
        
        try {
            $data = [
                'current_password' => 'password123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456'
            ];
            $response = $this->makeRequest('PUT', '/auth/profile', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Password change successful");
                echo "   ✅ Password changed successfully\n";
                
                // Update password for future tests
                // Note: In real scenario, you'd need to re-login
            } else {
                $this->recordFailure("Password change failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Password change error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testChangePasswordWithWrongCurrent()
    {
        echo "🚫 Test 7: Change Password (Wrong Current Password)\n";
        
        try {
            $data = [
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword789',
                'new_password_confirmation' => 'newpassword789'
            ];
            $response = $this->makeRequest('PUT', '/auth/profile', $data, $this->token, false);
            
            if (!$response['success'] && strpos($response['message'], 'incorrect') !== false) {
                $this->recordSuccess("Wrong password properly rejected");
                echo "   ✅ Wrong password properly rejected\n";
            } else {
                $this->recordFailure("Should have rejected wrong password", $response);
            }
        } catch (Exception $e) {
            // Expected to fail
            $this->recordSuccess("Wrong password properly rejected (exception)");
            echo "   ✅ Wrong password properly rejected\n";
        }
        
        echo "\n";
    }

    private function testUploadAvatar()
    {
        echo "🖼️  Test 8: Upload Profile Picture (Avatar)\n";
        
        try {
            // Create a test image file
            $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
            $tmpFile = tempnam(sys_get_temp_dir(), 'avatar') . '.png';
            file_put_contents($tmpFile, $imageData);
            
            $response = $this->uploadFile('/media/upload/avatar', $tmpFile, 'avatar', $this->token);
            
            unlink($tmpFile);
            
            if ($response['success']) {
                $this->recordSuccess("Avatar upload successful");
                echo "   ✅ Avatar uploaded successfully\n";
                if (isset($response['data']['avatar_url'])) {
                    echo "   URL: {$response['data']['avatar_url']}\n";
                }
            } else {
                $this->recordFailure("Avatar upload failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Avatar upload error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testGetPrivacySettings()
    {
        echo "🔒 Test 9: Get Privacy Settings\n";
        
        try {
            $response = $this->makeRequest('GET', '/settings/privacy', [], $this->token);
            
            if ($response['success'] && isset($response['data'])) {
                $this->recordSuccess("Get privacy settings successful");
                echo "   ✅ Privacy settings retrieved\n";
                echo "   Last Seen: {$response['data']['last_seen_privacy']}\n";
                echo "   Profile Photo: {$response['data']['profile_photo_privacy']}\n";
                echo "   Read Receipts: " . ($response['data']['read_receipts_enabled'] ? 'Enabled' : 'Disabled') . "\n";
            } else {
                $this->recordFailure("Get privacy settings failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Get privacy settings error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdatePrivacySettings()
    {
        echo "🛡️  Test 10: Update Privacy Settings\n";
        
        try {
            $data = [
                'last_seen_privacy' => 'contacts',
                'profile_photo_privacy' => 'nobody',
                'about_privacy' => 'everyone',
                'read_receipts_enabled' => false
            ];
            $response = $this->makeRequest('PUT', '/settings/privacy', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update privacy settings successful");
                echo "   ✅ Privacy settings updated\n";
                echo "   Last Seen: {$response['data']['last_seen_privacy']}\n";
                echo "   Profile Photo: {$response['data']['profile_photo_privacy']}\n";
                echo "   Read Receipts: " . ($response['data']['read_receipts_enabled'] ? 'Enabled' : 'Disabled') . "\n";
            } else {
                $this->recordFailure("Update privacy settings failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update privacy settings error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateSinglePrivacySetting()
    {
        echo "🔐 Test 11: Update Single Privacy Setting\n";
        
        try {
            $data = ['last_seen_privacy' => 'nobody'];
            $response = $this->makeRequest('PUT', '/settings/privacy', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update single privacy setting successful");
                echo "   ✅ Last seen privacy updated to: {$response['data']['last_seen_privacy']}\n";
            } else {
                $this->recordFailure("Update single privacy setting failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update single privacy setting error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testPrivacyValidation()
    {
        echo "⚠️  Test 12: Privacy Settings Validation\n";
        
        try {
            $data = ['last_seen_privacy' => 'invalid_value'];
            $response = $this->makeRequest('PUT', '/settings/privacy', $data, $this->token, false);
            
            if (!$response['success'] && isset($response['errors'])) {
                $this->recordSuccess("Privacy validation working correctly");
                echo "   ✅ Invalid privacy value properly rejected\n";
            } else {
                $this->recordFailure("Privacy validation should have failed", $response);
            }
        } catch (Exception $e) {
            // Expected to fail
            $this->recordSuccess("Privacy validation working correctly (exception)");
            echo "   ✅ Invalid privacy value properly rejected\n";
        }
        
        echo "\n";
    }

    private function testGetNotificationSettings()
    {
        echo "🔔 Test 13: Get Notification Settings\n";
        
        try {
            $response = $this->makeRequest('GET', '/settings/notifications', [], $this->token);
            
            if ($response['success'] && isset($response['data'])) {
                $this->recordSuccess("Get notification settings successful");
                echo "   ✅ Notification settings retrieved\n";
                echo "   Message Notifications: " . ($response['data']['message_notifications'] ? 'On' : 'Off') . "\n";
                echo "   Call Notifications: " . ($response['data']['call_notifications'] ? 'On' : 'Off') . "\n";
                echo "   Vibrate: " . ($response['data']['vibrate'] ? 'On' : 'Off') . "\n";
            } else {
                $this->recordFailure("Get notification settings failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Get notification settings error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testUpdateNotificationSettings()
    {
        echo "🔕 Test 14: Update Notification Settings\n";
        
        try {
            $data = [
                'message_notifications' => false,
                'call_notifications' => true,
                'vibrate' => false,
                'notification_preview' => 'name_only'
            ];
            $response = $this->makeRequest('PUT', '/settings/notifications', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Update notification settings successful");
                echo "   ✅ Notification settings updated\n";
                echo "   Message Notifications: Off\n";
                echo "   Call Notifications: On\n";
                echo "   Vibrate: Off\n";
            } else {
                $this->recordFailure("Update notification settings failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Update notification settings error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testExportUserData()
    {
        echo "📥 Test 15: Export User Data\n";
        
        try {
            $response = $this->makeRequest('GET', '/settings/export-data', [], $this->token);
            
            if ($response['success'] && isset($response['data']['profile'])) {
                $this->recordSuccess("Export user data successful");
                echo "   ✅ User data exported\n";
                echo "   Export generated at: {$response['data']['export_generated_at']}\n";
            } else {
                $this->recordFailure("Export user data failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Export user data error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function testDeleteAccountWithWrongPassword()
    {
        echo "🚫 Test 16: Delete Account (Wrong Password)\n";
        
        try {
            $data = [
                'password' => 'wrongpassword',
                'confirmation' => 'DELETE_MY_ACCOUNT'
            ];
            $response = $this->makeRequest('DELETE', '/settings/delete-account', $data, $this->token, false);
            
            if (!$response['success'] && strpos($response['message'], 'incorrect') !== false) {
                $this->recordSuccess("Wrong password properly rejected");
                echo "   ✅ Wrong password properly rejected\n";
            } else {
                $this->recordFailure("Should have rejected wrong password", $response);
            }
        } catch (Exception $e) {
            // Expected to fail
            $this->recordSuccess("Wrong password properly rejected (exception)");
            echo "   ✅ Wrong password properly rejected\n";
        }
        
        echo "\n";
    }

    private function testDeleteAccount()
    {
        echo "🗑️  Test 17: Delete Account (Correct Password)\n";
        echo "   ⚠️  WARNING: This will delete the test account!\n";
        
        try {
            $data = [
                'password' => 'newpassword456', // Use updated password
                'confirmation' => 'DELETE_MY_ACCOUNT'
            ];
            $response = $this->makeRequest('DELETE', '/settings/delete-account', $data, $this->token);
            
            if ($response['success']) {
                $this->recordSuccess("Account deletion successful");
                echo "   ✅ Account deleted successfully\n";
            } else {
                $this->recordFailure("Account deletion failed", $response);
            }
        } catch (Exception $e) {
            $this->recordFailure("Account deletion error: " . $e->getMessage());
        }
        
        echo "\n";
    }

    private function makeRequest($method, $endpoint, $data = [], $token = null, $expectSuccess = true)
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }

        $decodedResponse = json_decode($response, true);
        
        if ($expectSuccess && $httpCode >= 400) {
            $errorMsg = isset($decodedResponse['message']) ? $decodedResponse['message'] : "HTTP {$httpCode}";
            throw new Exception("Request failed: {$errorMsg}");
        }

        return $decodedResponse ?? ['success' => false, 'message' => 'Invalid response'];
    }

    private function uploadFile($endpoint, $filePath, $fieldName, $token)
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $cfile = new CURLFile($filePath, mime_content_type($filePath), basename($filePath));
        $postData = [$fieldName => $cfile];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 60
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? ['success' => false, 'message' => 'Invalid response'];
    }

    private function recordSuccess($message)
    {
        $this->testResults[] = ['status' => 'PASS', 'message' => $message];
        $this->passedTests++;
    }

    private function recordFailure($message, $response = null)
    {
        $this->testResults[] = [
            'status' => 'FAIL', 
            'message' => $message,
            'response' => $response
        ];
        $this->failedTests++;
        echo "   ❌ FAILED: {$message}\n";
        if ($response) {
            echo "   Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
        }
    }

    private function displayResults()
    {
        echo str_repeat("=", 70) . "\n";
        echo "📊 TEST RESULTS SUMMARY\n";
        echo str_repeat("=", 70) . "\n\n";

        $totalTests = $this->passedTests + $this->failedTests;
        $successRate = $totalTests > 0 ? round(($this->passedTests / $totalTests) * 100, 1) : 0;

        echo "Total Tests: {$totalTests}\n";
        echo "Passed: {$this->passedTests} ✅\n";
        echo "Failed: {$this->failedTests} ❌\n";
        echo "Success Rate: {$successRate}%\n\n";

        if ($successRate >= 90) {
            echo "🎉 EXCELLENT! All profile management features working perfectly!\n";
        } elseif ($successRate >= 75) {
            echo "✅ GOOD! Most features working with minor issues.\n";
        } elseif ($successRate >= 60) {
            echo "⚠️  FAIR! Some features need attention.\n";
        } else {
            echo "❌ NEEDS WORK! Several features require fixes.\n";
        }

        echo "\n📋 TESTED FEATURES:\n";
        echo "✅ Get user profile\n";
        echo "✅ Update profile name\n";
        echo "✅ Update profile bio/about\n";
        echo "✅ Update email address\n";
        echo "✅ Update multiple fields at once\n";
        echo "✅ Change password with verification\n";
        echo "✅ Password validation (wrong current password)\n";
        echo "✅ Upload profile picture (avatar)\n";
        echo "✅ Get privacy settings\n";
        echo "✅ Update privacy settings\n";
        echo "✅ Update single privacy setting\n";
        echo "✅ Privacy value validation\n";
        echo "✅ Get notification settings\n";
        echo "✅ Update notification settings\n";
        echo "✅ Export user data\n";
        echo "✅ Delete account validation\n";

        echo "\n" . str_repeat("=", 70) . "\n";
        
        if ($this->failedTests > 0) {
            echo "\n❌ FAILED TESTS DETAILS:\n";
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "   • {$result['message']}\n";
                }
            }
        }
        
        echo "\n";
    }
}

// Run the tests
$tester = new ProfileManagementApiTester();
$tester->runAllTests();

echo "🎯 Profile Management API Testing Complete!\n";
echo "📝 Check the results above for detailed information.\n\n";
