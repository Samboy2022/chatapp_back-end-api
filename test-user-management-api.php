<?php

/**
 * Comprehensive test script for User Management API endpoints
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "🔧 Testing User Management API Endpoints\n";
echo "========================================\n\n";

// Test configuration - generate unique credentials
$uniqueId = rand(100, 999);
$testUser = [
    'phone_number' => '+1234567' . $uniqueId,
    'password' => 'testpassword123',
    'email' => 'testuser' . $uniqueId . '@example.com',
    'name' => 'Test User ' . $uniqueId
];

$authToken = null;

// Helper functions
function makeRequest($method, $url, $data = [], $token = null, $isMultipart = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            // Don't set Content-Type for multipart, let curl handle it
        } else {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if (!empty($data)) {
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
        echo "❌ cURL Error: $error\n";
        return false;
    }
    
    echo "📊 HTTP Status: $httpCode\n";
    
    $data = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ Request successful\n";
            return $data;
        } else {
            echo "⚠️  Request completed but may have issues\n";
            return $data;
        }
    } else {
        echo "❌ Error response (status $httpCode)\n";
        if ($data && isset($data['message'])) {
            echo "Error: " . $data['message'] . "\n";
        }
        return false;
    }
}

// Test results storage
$results = [];

echo "🔐 Step 1: Authentication Setup\n";
echo "===============================\n";

// First, let's try to login with existing user or create one
echo "1.1 🔑 Testing Login\n";
echo "📞 Testing: $baseUrl/auth/login\n";
$loginData = [
    'login' => $testUser['phone_number'],
    'password' => $testUser['password']
];

$loginResult = makeRequest('POST', $baseUrl . '/auth/login', $loginData);

if (!$loginResult) {
    echo "Login failed, attempting to register new user...\n";
    
    echo "1.2 📝 Testing Registration\n";
    echo "📞 Testing: $baseUrl/auth/register\n";
    $registerData = [
        'name' => $testUser['name'],
        'email' => $testUser['email'],
        'phone_number' => $testUser['phone_number'],
        'country_code' => '+1',
        'password' => $testUser['password'],
        'password_confirmation' => $testUser['password']
    ];
    
    $registerResult = makeRequest('POST', $baseUrl . '/auth/register', $registerData);
    
    if ($registerResult && isset($registerResult['data']['token'])) {
        $authToken = $registerResult['data']['token'];
        echo "✅ Registration successful, token obtained\n";
        $results['Registration'] = true;
    } else {
        echo "❌ Registration failed\n";
        $results['Registration'] = false;
        echo "Cannot proceed with tests without authentication\n";
        exit(1);
    }
} else {
    if (isset($loginResult['data']['token'])) {
        $authToken = $loginResult['data']['token'];
        echo "✅ Login successful, token obtained\n";
        $results['Login'] = true;
    } else {
        echo "❌ Login failed - no token received\n";
        $results['Login'] = false;
        exit(1);
    }
}

echo "\n🔍 Step 2: User Profile Management APIs\n";
echo "=======================================\n";

// Test 2.1: Get user profile
echo "2.1 👤 Testing GET /api/settings/profile\n";
echo "📞 Testing: $baseUrl/settings/profile\n";
$profileResult = makeRequest('GET', $baseUrl . '/settings/profile', [], $authToken);
if ($profileResult) {
    echo "📊 User ID: " . $profileResult['data']['id'] . "\n";
    echo "📊 Name: " . $profileResult['data']['name'] . "\n";
    echo "📊 Phone: " . $profileResult['data']['phone_number'] . "\n";
    echo "📊 Email: " . $profileResult['data']['email'] . "\n";
    $results['Get Profile'] = true;
} else {
    $results['Get Profile'] = false;
}
echo "\n";

// Test 2.2: Update user profile
echo "2.2 ✏️  Testing POST /api/settings/profile\n";
echo "📞 Testing: $baseUrl/settings/profile\n";
$updateProfileData = [
    'name' => 'Updated Test User',
    'about' => 'This is my updated about section',
    'email' => 'updated.testuser' . $uniqueId . '@example.com'
];
$updateProfileResult = makeRequest('POST', $baseUrl . '/settings/profile', $updateProfileData, $authToken);
if ($updateProfileResult) {
    echo "📊 Profile updated successfully\n";
    echo "📊 New name: " . ($updateProfileResult['data']['name'] ?? 'N/A') . "\n";
    echo "📊 New about: " . ($updateProfileResult['data']['about'] ?? 'N/A') . "\n";
    $results['Update Profile'] = true;
} else {
    $results['Update Profile'] = false;
}
echo "\n";

// Test 2.3: Profile picture upload (simulate)
echo "2.3 📷 Testing Profile Picture Upload\n";
echo "📞 Testing: $baseUrl/settings/profile (with avatar)\n";
// Create a simple test image data
$testImageData = [
    'avatar' => new CURLFile(__FILE__, 'text/plain', 'test.txt') // Using this file as test
];
$avatarResult = makeRequest('POST', $baseUrl . '/settings/profile', $testImageData, $authToken, true);
if ($avatarResult) {
    echo "📊 Avatar upload test completed\n";
    $results['Avatar Upload'] = true;
} else {
    echo "⚠️  Avatar upload test - endpoint may not support file upload in current format\n";
    $results['Avatar Upload'] = false;
}
echo "\n";

echo "🔍 Step 3: Account Settings APIs\n";
echo "=================================\n";

// Test 3.1: Get privacy settings
echo "3.1 🔒 Testing GET /api/settings/privacy\n";
echo "📞 Testing: $baseUrl/settings/privacy\n";
$privacyResult = makeRequest('GET', $baseUrl . '/settings/privacy', [], $authToken);
if ($privacyResult) {
    echo "📊 Privacy settings retrieved\n";
    if (isset($privacyResult['data'])) {
        foreach ($privacyResult['data'] as $key => $value) {
            echo "  - $key: " . json_encode($value) . "\n";
        }
    }
    $results['Get Privacy'] = true;
} else {
    $results['Get Privacy'] = false;
}
echo "\n";

// Test 3.2: Update privacy settings
echo "3.2 🔒 Testing POST /api/settings/privacy\n";
echo "📞 Testing: $baseUrl/settings/privacy\n";
$privacyUpdateData = [
    'last_seen_privacy' => 'contacts',
    'profile_photo_privacy' => 'everyone',
    'about_privacy' => 'contacts',
    'read_receipts_enabled' => true
];
$privacyUpdateResult = makeRequest('POST', $baseUrl . '/settings/privacy', $privacyUpdateData, $authToken);
if ($privacyUpdateResult) {
    echo "📊 Privacy settings updated successfully\n";
    $results['Update Privacy'] = true;
} else {
    $results['Update Privacy'] = false;
}
echo "\n";

// Test 3.3: Get media settings
echo "3.3 📱 Testing GET /api/settings/media\n";
echo "📞 Testing: $baseUrl/settings/media-settings\n";
$mediaResult = makeRequest('GET', $baseUrl . '/settings/media-settings', [], $authToken);
if ($mediaResult) {
    echo "📊 Media settings retrieved\n";
    if (isset($mediaResult['data'])) {
        foreach ($mediaResult['data'] as $key => $value) {
            echo "  - $key: " . json_encode($value) . "\n";
        }
    }
    $results['Get Media Settings'] = true;
} else {
    $results['Get Media Settings'] = false;
}
echo "\n";

// Test 3.4: Update media settings
echo "3.4 📱 Testing POST /api/settings/media\n";
echo "📞 Testing: $baseUrl/settings/media-settings\n";
$mediaUpdateData = [
    'auto_download_photos' => true,
    'auto_download_videos' => false,
    'auto_download_documents' => true,
    'media_quality' => 'high'
];
$mediaUpdateResult = makeRequest('POST', $baseUrl . '/settings/media-settings', $mediaUpdateData, $authToken);
if ($mediaUpdateResult) {
    echo "📊 Media settings updated successfully\n";
    $results['Update Media Settings'] = true;
} else {
    $results['Update Media Settings'] = false;
}
echo "\n";

// Test 3.5: Get notification settings
echo "3.5 🔔 Testing GET /api/settings/notifications\n";
echo "📞 Testing: $baseUrl/settings/notifications\n";
$notificationResult = makeRequest('GET', $baseUrl . '/settings/notifications', [], $authToken);
if ($notificationResult) {
    echo "📊 Notification settings retrieved\n";
    if (isset($notificationResult['data'])) {
        foreach ($notificationResult['data'] as $key => $value) {
            echo "  - $key: " . json_encode($value) . "\n";
        }
    }
    $results['Get Notifications'] = true;
} else {
    $results['Get Notifications'] = false;
}
echo "\n";

// Test 3.6: Update notification settings
echo "3.6 🔔 Testing POST /api/settings/notifications\n";
echo "📞 Testing: $baseUrl/settings/notifications\n";
$notificationUpdateData = [
    'push_notifications' => true,
    'email_notifications' => false,
    'sms_notifications' => false,
    'notification_sound' => 'default',
    'vibration' => true
];
$notificationUpdateResult = makeRequest('POST', $baseUrl . '/settings/notifications', $notificationUpdateData, $authToken);
if ($notificationUpdateResult) {
    echo "📊 Notification settings updated successfully\n";
    $results['Update Notifications'] = true;
} else {
    $results['Update Notifications'] = false;
}
echo "\n";

echo "🔍 Step 4: Data Management APIs\n";
echo "===============================\n";

// Test 4.1: Export user data
echo "4.1 📤 Testing GET /api/settings/export\n";
echo "📞 Testing: $baseUrl/settings/export-data\n";
$exportResult = makeRequest('GET', $baseUrl . '/settings/export-data', [], $authToken);
if ($exportResult) {
    echo "📊 Data export completed\n";
    if (isset($exportResult['data'])) {
        echo "📊 Export includes: " . implode(', ', array_keys($exportResult['data'])) . "\n";
    }
    $results['Export Data'] = true;
} else {
    $results['Export Data'] = false;
}
echo "\n";

echo "🔍 Step 5: Contact Management & Synchronization\n";
echo "===============================================\n";

// Test 5.1: Get contacts list
echo "5.1 📞 Testing GET /api/contacts\n";
echo "📞 Testing: $baseUrl/contacts\n";
$contactsResult = makeRequest('GET', $baseUrl . '/contacts', [], $authToken);
if ($contactsResult) {
    echo "📊 Contacts retrieved successfully\n";
    if (isset($contactsResult['data']['data'])) {
        echo "📊 Total contacts: " . count($contactsResult['data']['data']) . "\n";
    }
    $results['Get Contacts'] = true;
} else {
    $results['Get Contacts'] = false;
}
echo "\n";

// Test 5.2: Sync device contacts
echo "5.2 🔄 Testing POST /api/contacts/sync\n";
echo "📞 Testing: $baseUrl/contacts/sync\n";
$syncData = [
    'contacts' => [
        [
            'name' => 'John Doe',
            'phone' => '+1234567891'
        ],
        [
            'name' => 'Jane Smith',
            'phone' => '+1234567892'
        ],
        [
            'name' => 'Bob Johnson',
            'phone' => '+1234567893'
        ]
    ]
];
$syncResult = makeRequest('POST', $baseUrl . '/contacts/sync', $syncData, $authToken);
if ($syncResult) {
    echo "📊 Contact sync completed\n";
    if (isset($syncResult['data'])) {
        echo "📊 Total synced: " . ($syncResult['data']['total_synced'] ?? 0) . "\n";
        echo "📊 New contacts: " . ($syncResult['data']['new_contacts'] ?? 0) . "\n";
        echo "📊 App users found: " . ($syncResult['data']['app_users_found'] ?? 0) . "\n";
    }
    $results['Sync Contacts'] = true;
} else {
    $results['Sync Contacts'] = false;
}
echo "\n";

// Test 5.3: Search contacts/users
echo "5.3 🔍 Testing GET /api/contacts/search\n";
echo "📞 Testing: $baseUrl/contacts/search\n";
$searchResult = makeRequest('GET', $baseUrl . '/contacts/search?query=test', [], $authToken);
if ($searchResult) {
    echo "📊 Contact search completed\n";
    if (isset($searchResult['data']['data'])) {
        echo "📊 Search results: " . count($searchResult['data']['data']) . "\n";
    }
    $results['Search Contacts'] = true;
} else {
    $results['Search Contacts'] = false;
}
echo "\n";

// Test 5.4: Get favorite contacts
echo "5.4 ⭐ Testing GET /api/contacts/favorites\n";
echo "📞 Testing: $baseUrl/contacts/favorites\n";
$favoritesResult = makeRequest('GET', $baseUrl . '/contacts/favorites', [], $authToken);
if ($favoritesResult) {
    echo "📊 Favorite contacts retrieved\n";
    if (isset($favoritesResult['data']['data'])) {
        echo "📊 Favorite contacts: " . count($favoritesResult['data']['data']) . "\n";
    }
    $results['Get Favorites'] = true;
} else {
    $results['Get Favorites'] = false;
}
echo "\n";

// Test 5.5: Get blocked contacts
echo "5.5 🚫 Testing GET /api/contacts/blocked\n";
echo "📞 Testing: $baseUrl/contacts/blocked\n";
$blockedResult = makeRequest('GET', $baseUrl . '/contacts/blocked', [], $authToken);
if ($blockedResult) {
    echo "📊 Blocked contacts retrieved\n";
    if (isset($blockedResult['data']['data'])) {
        echo "📊 Blocked contacts: " . count($blockedResult['data']['data']) . "\n";
    }
    $results['Get Blocked'] = true;
} else {
    $results['Get Blocked'] = false;
}
echo "\n";

echo "🔍 Step 6: Authentication & Security Tests\n";
echo "==========================================\n";

// Test 6.1: Test unauthorized access
echo "6.1 🔒 Testing Unauthorized Access\n";
echo "📞 Testing: $baseUrl/settings/profile (without token)\n";
$unauthorizedResult = makeRequest('GET', $baseUrl . '/settings/profile', []);
if (!$unauthorizedResult) {
    echo "✅ Correctly blocked unauthorized access\n";
    $results['Unauthorized Access'] = true;
} else {
    echo "⚠️  Should have blocked unauthorized access\n";
    $results['Unauthorized Access'] = false;
}
echo "\n";

// Test 6.2: Test invalid token
echo "6.2 🔒 Testing Invalid Token\n";
echo "📞 Testing: $baseUrl/settings/profile (with invalid token)\n";
$invalidTokenResult = makeRequest('GET', $baseUrl . '/settings/profile', [], 'invalid_token_123');
if (!$invalidTokenResult) {
    echo "✅ Correctly rejected invalid token\n";
    $results['Invalid Token'] = true;
} else {
    echo "⚠️  Should have rejected invalid token\n";
    $results['Invalid Token'] = false;
}
echo "\n";

echo "🔍 Step 7: Data Validation Tests\n";
echo "=================================\n";

// Test 7.1: Test invalid profile update
echo "7.1 ❌ Testing Invalid Profile Update\n";
echo "📞 Testing: $baseUrl/settings/profile (with invalid data)\n";
$invalidProfileData = [
    'name' => '', // Empty name should fail
    'email' => 'invalid-email', // Invalid email format
];
$invalidProfileResult = makeRequest('POST', $baseUrl . '/settings/profile', $invalidProfileData, $authToken);
if (!$invalidProfileResult) {
    echo "✅ Correctly rejected invalid profile data\n";
    $results['Invalid Profile Data'] = true;
} else {
    echo "⚠️  Should have rejected invalid profile data\n";
    $results['Invalid Profile Data'] = false;
}
echo "\n";

// Test 7.2: Test invalid privacy settings
echo "7.2 ❌ Testing Invalid Privacy Settings\n";
echo "📞 Testing: $baseUrl/settings/privacy (with invalid data)\n";
$invalidPrivacyData = [
    'last_seen_privacy' => 'invalid_option', // Invalid enum value (should be everyone, contacts, or nobody)
    'profile_photo_privacy' => 'invalid_value', // Invalid enum value
    'read_receipts_enabled' => 'not_boolean', // Invalid boolean
    'about_privacy' => 'invalid_choice', // Invalid enum value
];
$invalidPrivacyResult = makeRequest('POST', $baseUrl . '/settings/privacy', $invalidPrivacyData, $authToken);
if (!$invalidPrivacyResult) {
    echo "✅ Correctly rejected invalid privacy data\n";
    $results['Invalid Privacy Data'] = true;
} else {
    echo "⚠️  Should have rejected invalid privacy data\n";
    $results['Invalid Privacy Data'] = false;
}
echo "\n";

// Display results summary
echo "📋 USER MANAGEMENT API TEST SUMMARY\n";
echo "====================================\n";
foreach ($results as $test => $passed) {
    $status = $passed ? '✅ PASS' : '❌ FAIL';
    echo "$status $test\n";
}

$totalTests = count($results);
$passedTests = count(array_filter($results));
$failedTests = $totalTests - $passedTests;

echo "\n🎯 Test Results:\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: $failedTests\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";

if ($failedTests === 0) {
    echo "\n🎉 All tests passed! User Management API is working correctly.\n";
} else {
    echo "\n⚠️  Some tests failed. Please check the implementation.\n";
}

// Test 8: Account Deletion (commented out for safety)
echo "\n⚠️  Account Deletion Test (Skipped for Safety)\n";
echo "===============================================\n";
echo "Account deletion test is available but skipped to prevent data loss.\n";
echo "To test account deletion, uncomment the following code:\n";
echo "/*\n";
echo "echo \"8.1 🗑️  Testing DELETE /api/settings/account\\n\";\n";
echo "echo \"📞 Testing: \$baseUrl/settings/delete-account\\n\";\n";
echo "\$deleteData = ['password' => \$testUser['password'], 'confirmation' => 'DELETE'];\n";
echo "\$deleteResult = makeRequest('POST', \$baseUrl . '/settings/delete-account', \$deleteData, \$authToken);\n";
echo "*/\n";

echo "\n🌐 User Management API testing completed!\n";
echo "📖 Next: Update API documentation with phone number authentication\n";
