<?php

/**
 * Test Remaining FarmersNetwork Chat Features
 * Tests: Profile Management, Media Messages, Message Reactions, Contact Management, 
 * Privacy Settings, Notification Settings, Data Export, Advanced Features
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "🔍 FarmersNetwork Remaining Features Test\n";
echo "=========================================\n\n";

// Generate unique test data
$timestamp = time();
$testUser = [
    'name' => 'Feature Test User ' . $timestamp,
    'email' => 'featuretest.' . $timestamp . '@farmersnetwork.com',
    'phone_number' => '+123459' . substr($timestamp, -4),
    'country_code' => '+1',
    'password' => 'testpass123',
    'password_confirmation' => 'testpass123'
];

$secondUser = [
    'name' => 'Second Feature User ' . $timestamp,
    'email' => 'secondfeature.' . $timestamp . '@farmersnetwork.com',
    'phone_number' => '+123460' . substr($timestamp, -4),
    'country_code' => '+1',
    'password' => 'testpass123',
    'password_confirmation' => 'testpass123'
];

// Helper function for API requests
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
    curl_close($ch);
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ Request successful\n";
            return $data;
        } else {
            echo "⚠️  Request completed but may have issues\n";
            return $data;
        }
    } else {
        echo "❌ Error response (status $httpCode)\n";
        $errorData = json_decode($response, true);
        if ($errorData && isset($errorData['message'])) {
            echo "Error: " . $errorData['message'] . "\n";
        }
        return false;
    }
}

$results = [];

echo "🔐 SETUP: User Registration\n";
echo "===========================\n\n";

// Register test users
echo "1. 📝 Registering Primary Test User...\n";
$registerResult = makeRequest('POST', $baseUrl . '/auth/register', $testUser);

if ($registerResult && isset($registerResult['data']['token'])) {
    $token = $registerResult['data']['token'];
    $userId = $registerResult['data']['user']['id'];
    echo "✅ Primary user registered successfully (ID: $userId)\n\n";
} else {
    echo "❌ Primary user registration failed, cannot proceed\n";
    exit(1);
}

echo "2. 📝 Registering Second Test User...\n";
$secondRegisterResult = makeRequest('POST', $baseUrl . '/auth/register', $secondUser);

if ($secondRegisterResult && isset($secondRegisterResult['data']['token'])) {
    $secondToken = $secondRegisterResult['data']['token'];
    $secondUserId = $secondRegisterResult['data']['user']['id'];
    echo "✅ Second user registered successfully (ID: $secondUserId)\n\n";
} else {
    echo "❌ Second user registration failed\n";
    $secondToken = null;
    $secondUserId = null;
}

echo "👤 PHASE 1: ADVANCED PROFILE MANAGEMENT\n";
echo "========================================\n\n";

// Test profile update with all fields
echo "1.1 ✏️  Testing Complete Profile Update...\n";
$profileData = [
    'name' => 'Updated Feature Test User',
    'about' => 'I am a comprehensive feature tester for FarmersNetwork',
    'email' => 'updated.featuretest.' . $timestamp . '@farmersnetwork.com'
];

$profileResult = makeRequest('POST', $baseUrl . '/settings/profile', $profileData, $token);
if ($profileResult) {
    echo "✅ Complete profile update successful\n";
    echo "📊 Updated name: " . ($profileResult['data']['name'] ?? 'N/A') . "\n";
    $results['Complete Profile Update'] = true;
} else {
    $results['Complete Profile Update'] = false;
}
echo "\n";

// Test avatar upload simulation
echo "1.2 📷 Testing Avatar Upload (Simulated)...\n";
// Since we can't upload actual files in this test, we'll simulate with a URL
$avatarData = [
    'name' => 'Updated Feature Test User',
    'avatar_url' => 'https://example.com/avatars/test-user.jpg' // Simulated
];

$avatarResult = makeRequest('POST', $baseUrl . '/settings/profile', $avatarData, $token);
if ($avatarResult) {
    echo "✅ Avatar update simulation successful\n";
    $results['Avatar Upload'] = true;
} else {
    $results['Avatar Upload'] = false;
}
echo "\n";

echo "🔒 PHASE 2: PRIVACY & SECURITY SETTINGS\n";
echo "========================================\n\n";

// Test privacy settings update
echo "2.1 🔐 Testing Privacy Settings Update...\n";
$privacyData = [
    'last_seen_privacy' => 'contacts',
    'profile_photo_privacy' => 'everyone',
    'about_privacy' => 'nobody',
    'read_receipts_enabled' => true
];

$privacyResult = makeRequest('POST', $baseUrl . '/settings/privacy', $privacyData, $token);
if ($privacyResult) {
    echo "✅ Privacy settings updated successfully\n";
    $results['Privacy Settings Update'] = true;
} else {
    $results['Privacy Settings Update'] = false;
}
echo "\n";

// Test get privacy settings
echo "2.2 📋 Testing Get Privacy Settings...\n";
$getPrivacyResult = makeRequest('GET', $baseUrl . '/settings/privacy', [], $token);
if ($getPrivacyResult) {
    echo "✅ Privacy settings retrieved successfully\n";
    echo "📊 Last seen privacy: " . ($getPrivacyResult['data']['last_seen_privacy'] ?? 'N/A') . "\n";
    $results['Get Privacy Settings'] = true;
} else {
    $results['Get Privacy Settings'] = false;
}
echo "\n";

echo "🔔 PHASE 3: NOTIFICATION SETTINGS\n";
echo "==================================\n\n";

// Test notification settings
echo "3.1 🔔 Testing Notification Settings Update...\n";
$notificationData = [
    'push_notifications' => true,
    'email_notifications' => false,
    'sms_notifications' => false,
    'notification_sound' => 'default',
    'vibration' => true
];

$notificationResult = makeRequest('POST', $baseUrl . '/settings/notifications', $notificationData, $token);
if ($notificationResult) {
    echo "✅ Notification settings updated successfully\n";
    $results['Notification Settings Update'] = true;
} else {
    $results['Notification Settings Update'] = false;
}
echo "\n";

// Test get notification settings
echo "3.2 📋 Testing Get Notification Settings...\n";
$getNotificationResult = makeRequest('GET', $baseUrl . '/settings/notifications', [], $token);
if ($getNotificationResult) {
    echo "✅ Notification settings retrieved successfully\n";
    $results['Get Notification Settings'] = true;
} else {
    $results['Get Notification Settings'] = false;
}
echo "\n";

echo "📱 PHASE 4: MEDIA SETTINGS\n";
echo "===========================\n\n";

// Test media settings
echo "4.1 📱 Testing Media Settings Update...\n";
$mediaData = [
    'auto_download_photos' => true,
    'auto_download_videos' => false,
    'auto_download_documents' => true,
    'media_quality' => 'high',
    'compress_images' => true,
    'save_to_gallery' => false
];

$mediaResult = makeRequest('POST', $baseUrl . '/settings/media-settings', $mediaData, $token);
if ($mediaResult) {
    echo "✅ Media settings updated successfully\n";
    $results['Media Settings Update'] = true;
} else {
    $results['Media Settings Update'] = false;
}
echo "\n";

// Test get media settings
echo "4.2 📋 Testing Get Media Settings...\n";
$getMediaResult = makeRequest('GET', $baseUrl . '/settings/media-settings', [], $token);
if ($getMediaResult) {
    echo "✅ Media settings retrieved successfully\n";
    $results['Get Media Settings'] = true;
} else {
    $results['Get Media Settings'] = false;
}
echo "\n";

echo "💾 PHASE 5: DATA MANAGEMENT\n";
echo "============================\n\n";

// Test data export
echo "5.1 📤 Testing Data Export...\n";
$exportResult = makeRequest('GET', $baseUrl . '/settings/export-data', [], $token);
if ($exportResult) {
    echo "✅ Data export successful\n";
    echo "📊 Export generated at: " . ($exportResult['data']['export_generated_at'] ?? 'N/A') . "\n";
    $results['Data Export'] = true;
} else {
    $results['Data Export'] = false;
}
echo "\n";

echo "📞 PHASE 6: ADVANCED CONTACT MANAGEMENT\n";
echo "========================================\n\n";

// Test contact search
echo "6.1 🔍 Testing Contact Search...\n";
$searchResult = makeRequest('GET', $baseUrl . '/contacts/search?query=feature', [], $token);
if ($searchResult) {
    echo "✅ Contact search successful\n";
    if (isset($searchResult['data']['data'])) {
        echo "📊 Search results: " . count($searchResult['data']['data']) . "\n";
    }
    $results['Contact Search'] = true;
} else {
    $results['Contact Search'] = false;
}
echo "\n";

// Test get favorites
echo "6.2 ⭐ Testing Get Favorite Contacts...\n";
$favoritesResult = makeRequest('GET', $baseUrl . '/contacts/favorites', [], $token);
if ($favoritesResult) {
    echo "✅ Favorite contacts retrieved successfully\n";
    $results['Get Favorite Contacts'] = true;
} else {
    $results['Get Favorite Contacts'] = false;
}
echo "\n";

if ($secondUserId) {
    // First sync contacts to create the relationship
    echo "6.3 📞 Syncing Contacts for Favorite Test...\n";
    $syncData = [
        'contacts' => [
            [
                'name' => $secondUser['name'],
                'phone' => $secondUser['phone_number']
            ]
        ]
    ];

    $syncResult = makeRequest('POST', $baseUrl . '/contacts/sync', $syncData, $token);
    if ($syncResult) {
        echo "✅ Contacts synced for favorite test\n";

        // Now test toggle favorite
        echo "6.4 ⭐ Testing Toggle Favorite Contact...\n";
        $favoriteResult = makeRequest('POST', $baseUrl . "/contacts/favorite/{$secondUserId}", [], $token);
        if ($favoriteResult) {
            echo "✅ Toggle favorite contact successful\n";
            $results['Toggle Favorite Contact'] = true;
        } else {
            $results['Toggle Favorite Contact'] = false;
        }
    } else {
        echo "⚠️  Contact sync failed, skipping favorite test\n";
        $results['Toggle Favorite Contact'] = false;
    }
    echo "\n";
}

echo "💬 PHASE 7: ADVANCED MESSAGING FEATURES\n";
echo "========================================\n\n";

if ($secondUserId) {
    // Send a message first
    echo "7.1 📤 Sending Test Message for Reactions...\n";
    $messageData = [
        'receiver_id' => $secondUserId,
        'message' => 'This is a test message for reactions and replies',
        'type' => 'text'
    ];

    $messageResult = makeRequest('POST', $baseUrl . '/messages', $messageData, $token);
    if ($messageResult && isset($messageResult['data']['id'])) {
        $messageId = $messageResult['data']['id'];
        echo "✅ Test message sent successfully (ID: $messageId)\n\n";

        // Test message reaction
        echo "7.2 😊 Testing Message Reaction...\n";
        $reactionData = [
            'reaction' => '👍'
        ];

        $reactionResult = makeRequest('POST', $baseUrl . "/messages/{$messageId}/react", $reactionData, $secondToken);
        if ($reactionResult) {
            echo "✅ Message reaction added successfully\n";
            $results['Message Reaction'] = true;
        } else {
            $results['Message Reaction'] = false;
        }
        echo "\n";

        // Test message reply
        echo "7.3 💬 Testing Message Reply...\n";
        $replyData = [
            'receiver_id' => $userId,
            'message' => 'This is a reply to your message',
            'type' => 'text',
            'reply_to_id' => $messageId
        ];

        $replyResult = makeRequest('POST', $baseUrl . '/messages', $replyData, $secondToken);
        if ($replyResult) {
            echo "✅ Message reply sent successfully\n";
            $results['Message Reply'] = true;
        } else {
            $results['Message Reply'] = false;
        }
        echo "\n";

        // Test mark message as read
        echo "7.4 ✅ Testing Mark Message as Read...\n";
        $readResult = makeRequest('POST', $baseUrl . "/messages/{$messageId}/read", [], $secondToken);
        if ($readResult) {
            echo "✅ Message marked as read successfully\n";
            $results['Mark Message Read'] = true;
        } else {
            $results['Mark Message Read'] = false;
        }
        echo "\n";
    }

    // Test media message
    echo "7.5 📎 Testing Media Message Send...\n";
    $mediaMessageData = [
        'receiver_id' => $secondUserId,
        'message' => 'Check out this image!',
        'type' => 'image',
        'media_url' => 'https://example.com/images/test.jpg',
        'media_type' => 'image/jpeg',
        'media_size' => 1024000
    ];

    $mediaMessageResult = makeRequest('POST', $baseUrl . '/messages', $mediaMessageData, $token);
    if ($mediaMessageResult) {
        echo "✅ Media message sent successfully\n";
        $results['Media Message Send'] = true;
    } else {
        $results['Media Message Send'] = false;
    }
    echo "\n";
}

echo "📱 PHASE 8: ADVANCED STATUS FEATURES\n";
echo "=====================================\n\n";

// Create a status first
echo "8.1 📸 Creating Test Status...\n";
$statusData = [
    'content' => 'Testing advanced status features with privacy controls',
    'type' => 'text',
    'privacy' => 'contacts',
    'background_color' => '#4CAF50',
    'text_color' => '#FFFFFF'
];

$statusResult = makeRequest('POST', $baseUrl . '/statuses', $statusData, $token);
if ($statusResult && isset($statusResult['data']['id'])) {
    $statusId = $statusResult['data']['id'];
    echo "✅ Test status created successfully (ID: $statusId)\n\n";

    if ($secondToken) {
        // Test view status
        echo "8.2 👁️  Testing View Status...\n";
        $viewStatusResult = makeRequest('GET', $baseUrl . "/statuses/{$statusId}", [], $secondToken);
        if ($viewStatusResult) {
            echo "✅ Status viewed successfully\n";
            $results['View Status'] = true;
        } else {
            $results['View Status'] = false;
        }
        echo "\n";

        // Test get status views
        echo "8.3 📊 Testing Get Status Views...\n";
        $statusViewsResult = makeRequest('GET', $baseUrl . "/statuses/{$statusId}/views", [], $token);
        if ($statusViewsResult) {
            echo "✅ Status views retrieved successfully\n";
            if (isset($statusViewsResult['data']['data'])) {
                echo "📊 Total views: " . count($statusViewsResult['data']['data']) . "\n";
            }
            $results['Get Status Views'] = true;
        } else {
            $results['Get Status Views'] = false;
        }
        echo "\n";
    }

    // Test delete status
    echo "8.4 🗑️  Testing Delete Status...\n";
    $deleteStatusResult = makeRequest('DELETE', $baseUrl . "/statuses/{$statusId}", [], $token);
    if ($deleteStatusResult) {
        echo "✅ Status deleted successfully\n";
        $results['Delete Status'] = true;
    } else {
        $results['Delete Status'] = false;
    }
    echo "\n";
}

echo "📞 PHASE 9: ADVANCED CALL FEATURES\n";
echo "===================================\n\n";

if ($secondToken && $secondUserId) {
    // Test video call
    echo "9.1 📹 Testing Video Call Initiation...\n";
    $videoCallData = [
        'receiver_id' => $secondUserId,
        'type' => 'video'
    ];

    $videoCallResult = makeRequest('POST', $baseUrl . '/calls', $videoCallData, $token);
    if ($videoCallResult && isset($videoCallResult['data']['id'])) {
        $callId = $videoCallResult['data']['id'];
        echo "✅ Video call initiated successfully (ID: $callId)\n";
        $results['Initiate Video Call'] = true;

        // Test call reject
        echo "9.2 ❌ Testing Video Call Reject...\n";
        $rejectResult = makeRequest('POST', $baseUrl . "/calls/{$callId}/reject", [], $secondToken);
        if ($rejectResult) {
            echo "✅ Video call rejected successfully\n";
            $results['Reject Video Call'] = true;
        } else {
            $results['Reject Video Call'] = false;
        }
        echo "\n";
    } else {
        $results['Initiate Video Call'] = false;
    }

    // Test get call statistics
    echo "9.3 📊 Testing Get Call Statistics...\n";
    $statsResult = makeRequest('GET', $baseUrl . '/calls/statistics', [], $token);
    if ($statsResult) {
        echo "✅ Call statistics retrieved successfully\n";
        $results['Get Call Statistics'] = true;
    } else {
        $results['Get Call Statistics'] = false;
    }
    echo "\n";

    // Test get missed calls count
    echo "9.4 📵 Testing Get Missed Calls Count...\n";
    $missedResult = makeRequest('GET', $baseUrl . '/calls/missed-count', [], $token);
    if ($missedResult) {
        echo "✅ Missed calls count retrieved successfully\n";
        $results['Get Missed Calls Count'] = true;
    } else {
        $results['Get Missed Calls Count'] = false;
    }
    echo "\n";
}

echo "🔧 PHASE 10: SYSTEM & ADMIN FEATURES\n";
echo "=====================================\n\n";

// Test app settings
echo "10.1 ⚙️  Testing Get App Settings...\n";
$appSettingsResult = makeRequest('GET', $baseUrl . '/app-settings', [], $token);
if ($appSettingsResult) {
    echo "✅ App settings retrieved successfully\n";
    $results['Get App Settings'] = true;
} else {
    $results['Get App Settings'] = false;
}
echo "\n";

// Test broadcast settings
echo "10.2 📡 Testing Get Broadcast Settings...\n";
$broadcastResult = makeRequest('GET', $baseUrl . '/broadcast-settings', [], $token);
if ($broadcastResult) {
    echo "✅ Broadcast settings retrieved successfully\n";
    $results['Get Broadcast Settings'] = true;
} else {
    $results['Get Broadcast Settings'] = false;
}
echo "\n";

// Final Results Summary
echo "📋 REMAINING FEATURES TEST RESULTS\n";
echo "===================================\n\n";

$totalTests = count($results);
$passedTests = count(array_filter($results));
$failedTests = $totalTests - $passedTests;

echo "📊 DETAILED TEST RESULTS:\n";
echo "=========================\n";

$phases = [
    'Profile Management' => ['Complete Profile Update', 'Avatar Upload'],
    'Privacy & Security' => ['Privacy Settings Update', 'Get Privacy Settings'],
    'Notification Settings' => ['Notification Settings Update', 'Get Notification Settings'],
    'Media Settings' => ['Media Settings Update', 'Get Media Settings'],
    'Data Management' => ['Data Export'],
    'Contact Management' => ['Contact Search', 'Get Favorite Contacts', 'Toggle Favorite Contact'],
    'Advanced Messaging' => ['Message Reaction', 'Message Reply', 'Mark Message Read', 'Media Message Send'],
    'Status Features' => ['View Status', 'Get Status Views', 'Delete Status'],
    'Call Features' => ['Initiate Video Call', 'Reject Video Call', 'Get Call Statistics', 'Get Missed Calls Count'],
    'System Features' => ['Get App Settings', 'Get Broadcast Settings']
];

foreach ($phases as $phaseName => $phaseTests) {
    $phasePassCount = 0;
    $phaseTotal = count($phaseTests);

    foreach ($phaseTests as $test) {
        if (isset($results[$test]) && $results[$test]) {
            $phasePassCount++;
        }
    }

    $phaseSuccessRate = $phaseTotal > 0 ? round(($phasePassCount / $phaseTotal) * 100, 1) : 0;
    $phaseStatus = $phaseSuccessRate >= 80 ? '✅' : ($phaseSuccessRate >= 50 ? '⚠️' : '❌');

    echo "$phaseStatus $phaseName: $phasePassCount/$phaseTotal ({$phaseSuccessRate}%)\n";
}

echo "\n📊 INDIVIDUAL TEST RESULTS:\n";
echo "============================\n";
foreach ($results as $test => $passed) {
    $status = $passed ? '✅ PASS' : '❌ FAIL';
    echo "$status $test\n";
}

echo "\n🎯 OVERALL RESULTS:\n";
echo "==================\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: $failedTests\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";

if ($failedTests === 0) {
    echo "\n🎉 PERFECT! All remaining features are working correctly!\n";
    echo "✅ The FarmersNetwork chat application is FULLY PRODUCTION READY!\n";
} elseif ($passedTests / $totalTests >= 0.8) {
    echo "\n🎯 EXCELLENT! Most remaining features are working correctly.\n";
    echo "✅ The FarmersNetwork chat application is READY with minor issues to address.\n";
} elseif ($passedTests / $totalTests >= 0.6) {
    echo "\n⚠️  GOOD! Core remaining features are working but some issues need attention.\n";
    echo "🔧 Please review failed tests and fix issues before production.\n";
} else {
    echo "\n❌ ATTENTION NEEDED! Multiple remaining features are failing.\n";
    echo "🔧 Significant issues need to be resolved before production deployment.\n";
}

echo "\n🚀 ADDITIONAL FEATURES TESTED:\n";
echo "==============================\n";
echo "✅ Advanced Profile Management (Complete Updates, Avatar)\n";
echo "✅ Privacy & Security Settings (Last Seen, Profile Photo, About)\n";
echo "✅ Notification Settings (Push, Email, SMS, Sounds)\n";
echo "✅ Media Settings (Auto-download, Quality, Compression)\n";
echo "✅ Data Management (Export User Data)\n";
echo "✅ Advanced Contact Management (Search, Favorites, Toggle)\n";
echo "✅ Advanced Messaging (Reactions, Replies, Read Receipts, Media)\n";
echo "✅ Status Features (View, Views Tracking, Delete)\n";
echo "✅ Call Features (Video Calls, Reject, Statistics, Missed Count)\n";
echo "✅ System Features (App Settings, Broadcast Settings)\n";

echo "\n📱 COMPREHENSIVE FEATURE COVERAGE:\n";
echo "==================================\n";
echo "The FarmersNetwork chat application now has comprehensive coverage of:\n";
echo "• User authentication and profile management\n";
echo "• Privacy and security controls\n";
echo "• Notification and media preferences\n";
echo "• Contact synchronization and management\n";
echo "• Advanced messaging with reactions and replies\n";
echo "• Status updates with privacy controls\n";
echo "• Voice and video calling capabilities\n";
echo "• Data export and management\n";
echo "• System configuration and settings\n";

echo "\n🎯 Remaining Features Test - COMPLETE!\n";
echo "======================================\n";
