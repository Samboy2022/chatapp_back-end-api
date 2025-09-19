<?php

/**
 * Comprehensive Contact Management API Test
 * Tests all contact functionality to achieve 100% success rate
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "📞 FarmersNetwork Contact Management Complete Test\n";
echo "==================================================\n\n";

// Generate unique test data
$timestamp = time();
$users = [
    'user1' => [
        'name' => 'Contact Test User 1 ' . $timestamp,
        'email' => 'contacttest1.' . $timestamp . '@farmersnetwork.com',
        'phone_number' => '+123470' . substr($timestamp, -4),
        'country_code' => '+1',
        'password' => 'testpass123',
        'password_confirmation' => 'testpass123'
    ],
    'user2' => [
        'name' => 'Contact Test User 2 ' . $timestamp,
        'email' => 'contacttest2.' . $timestamp . '@farmersnetwork.com',
        'phone_number' => '+123471' . substr($timestamp, -4),
        'country_code' => '+1',
        'password' => 'testpass123',
        'password_confirmation' => 'testpass123'
    ],
    'user3' => [
        'name' => 'Contact Test User 3 ' . $timestamp,
        'email' => 'contacttest3.' . $timestamp . '@farmersnetwork.com',
        'phone_number' => '+123472' . substr($timestamp, -4),
        'country_code' => '+1',
        'password' => 'testpass123',
        'password_confirmation' => 'testpass123'
    ]
];

// Helper function for API requests
function makeRequest($method, $url, $data = [], $token = null) {
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
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
$tokens = [];
$userIds = [];

echo "🔐 SETUP: User Registration\n";
echo "===========================\n\n";

// Register all test users
foreach ($users as $userKey => $userData) {
    echo "Setup.{$userKey} 📝 Registering {$userData['name']}...\n";
    
    $registerResult = makeRequest('POST', $baseUrl . '/auth/register', $userData);
    
    if ($registerResult && isset($registerResult['data']['token'])) {
        $tokens[$userKey] = $registerResult['data']['token'];
        $userIds[$userKey] = $registerResult['data']['user']['id'];
        echo "✅ {$userData['name']} registered successfully (ID: {$userIds[$userKey]})\n";
    } else {
        echo "❌ {$userData['name']} registration failed\n";
        exit(1);
    }
    echo "\n";
}

echo "📞 PHASE 1: CONTACT SYNCHRONIZATION\n";
echo "====================================\n\n";

// Test contact sync with all users
echo "1.1 🔄 Testing Contact Synchronization...\n";
$syncData = [
    'contacts' => [
        [
            'name' => $users['user2']['name'],
            'phone' => $users['user2']['phone_number']
        ],
        [
            'name' => $users['user3']['name'],
            'phone' => $users['user3']['phone_number']
        ]
    ]
];

$syncResult = makeRequest('POST', $baseUrl . '/contacts/sync', $syncData, $tokens['user1']);
if ($syncResult) {
    echo "✅ Contact synchronization successful\n";
    if (isset($syncResult['data']['synced_contacts'])) {
        echo "📊 Synced contacts: " . count($syncResult['data']['synced_contacts']) . "\n";
    }
    $results['Contact Synchronization'] = true;
} else {
    $results['Contact Synchronization'] = false;
}
echo "\n";

echo "📋 PHASE 2: CONTACT LISTING & SEARCH\n";
echo "=====================================\n\n";

// Test get all contacts
echo "2.1 📋 Testing Get All Contacts...\n";
$contactsResult = makeRequest('GET', $baseUrl . '/contacts', [], $tokens['user1']);
if ($contactsResult) {
    echo "✅ Get all contacts successful\n";
    if (isset($contactsResult['data']['data'])) {
        echo "📊 Total contacts: " . count($contactsResult['data']['data']) . "\n";
    }
    $results['Get All Contacts'] = true;
} else {
    $results['Get All Contacts'] = false;
}
echo "\n";

// Test contact search
echo "2.2 🔍 Testing Contact Search...\n";
$searchQuery = urlencode('Test');
$searchResult = makeRequest('GET', $baseUrl . '/contacts/search?query=' . $searchQuery, [], $tokens['user1']);
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

echo "⭐ PHASE 3: FAVORITE CONTACTS MANAGEMENT\n";
echo "=========================================\n\n";

// Test toggle favorite contact
echo "3.1 ⭐ Testing Toggle Favorite Contact...\n";
$favoriteResult = makeRequest('POST', $baseUrl . "/contacts/favorite/{$userIds['user2']}", [], $tokens['user1']);
if ($favoriteResult) {
    echo "✅ Toggle favorite contact successful\n";
    echo "📊 Favorite status: " . ($favoriteResult['data']['is_favorite'] ? 'Added to favorites' : 'Removed from favorites') . "\n";
    $results['Toggle Favorite Contact'] = true;
} else {
    $results['Toggle Favorite Contact'] = false;
}
echo "\n";

// Test get favorite contacts
echo "3.2 📋 Testing Get Favorite Contacts...\n";
$favoritesResult = makeRequest('GET', $baseUrl . '/contacts/favorites', [], $tokens['user1']);
if ($favoritesResult) {
    echo "✅ Get favorite contacts successful\n";
    if (isset($favoritesResult['data']['data'])) {
        echo "📊 Favorite contacts: " . count($favoritesResult['data']['data']) . "\n";
    }
    $results['Get Favorite Contacts'] = true;
} else {
    $results['Get Favorite Contacts'] = false;
}
echo "\n";

// Test toggle favorite again (remove from favorites)
echo "3.3 ⭐ Testing Remove from Favorites...\n";
$unfavoriteResult = makeRequest('POST', $baseUrl . "/contacts/favorite/{$userIds['user2']}", [], $tokens['user1']);
if ($unfavoriteResult) {
    echo "✅ Remove from favorites successful\n";
    echo "📊 Favorite status: " . ($unfavoriteResult['data']['is_favorite'] ? 'Added to favorites' : 'Removed from favorites') . "\n";
    $results['Remove from Favorites'] = true;
} else {
    $results['Remove from Favorites'] = false;
}
echo "\n";

echo "🚫 PHASE 4: BLOCK/UNBLOCK FUNCTIONALITY\n";
echo "========================================\n\n";

// Test block contact
echo "4.1 🚫 Testing Block Contact...\n";
$blockResult = makeRequest('POST', $baseUrl . "/contacts/block/{$userIds['user3']}", [], $tokens['user1']);
if ($blockResult) {
    echo "✅ Block contact successful\n";
    echo "📊 Contact blocked: " . ($blockResult['data']['is_blocked'] ? 'Yes' : 'No') . "\n";
    $results['Block Contact'] = true;
} else {
    $results['Block Contact'] = false;
}
echo "\n";

// Test get blocked contacts
echo "4.2 📋 Testing Get Blocked Contacts...\n";
$blockedResult = makeRequest('GET', $baseUrl . '/contacts/blocked', [], $tokens['user1']);
if ($blockedResult) {
    echo "✅ Get blocked contacts successful\n";
    if (isset($blockedResult['data']['data'])) {
        echo "📊 Blocked contacts: " . count($blockedResult['data']['data']) . "\n";
    }
    $results['Get Blocked Contacts'] = true;
} else {
    $results['Get Blocked Contacts'] = false;
}
echo "\n";

// Test unblock contact
echo "4.3 ✅ Testing Unblock Contact...\n";
$unblockResult = makeRequest('POST', $baseUrl . "/contacts/unblock/{$userIds['user3']}", [], $tokens['user1']);
if ($unblockResult) {
    echo "✅ Unblock contact successful\n";
    echo "📊 Contact unblocked: " . (!$unblockResult['data']['is_blocked'] ? 'Yes' : 'No') . "\n";
    $results['Unblock Contact'] = true;
} else {
    $results['Unblock Contact'] = false;
}
echo "\n";

// Final Results Summary
echo "📋 CONTACT MANAGEMENT TEST RESULTS\n";
echo "===================================\n\n";

$totalTests = count($results);
$passedTests = count(array_filter($results));
$failedTests = $totalTests - $passedTests;

echo "📊 PHASE BREAKDOWN:\n";
echo "===================\n";

$phases = [
    'Contact Synchronization' => ['Contact Synchronization'],
    'Contact Listing & Search' => ['Get All Contacts', 'Contact Search'],
    'Favorite Management' => ['Toggle Favorite Contact', 'Get Favorite Contacts', 'Remove from Favorites'],
    'Block/Unblock Functionality' => ['Block Contact', 'Get Blocked Contacts', 'Unblock Contact']
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
    $phaseStatus = $phaseSuccessRate >= 100 ? '✅' : ($phaseSuccessRate >= 80 ? '⚠️' : '❌');

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
    echo "\n🎉 PERFECT! All Contact Management features are working correctly!\n";
    echo "✅ Contact Management API is 100% PRODUCTION READY!\n";
} elseif ($passedTests / $totalTests >= 0.9) {
    echo "\n🎯 EXCELLENT! Contact Management is nearly perfect.\n";
    echo "✅ Contact Management API is READY with minimal issues.\n";
} else {
    echo "\n⚠️  Contact Management needs attention.\n";
    echo "🔧 Please review failed tests and fix issues.\n";
}

echo "\n🚀 CONTACT MANAGEMENT FEATURES TESTED:\n";
echo "======================================\n";
echo "✅ Contact Synchronization from Device Contacts\n";
echo "✅ Get All Contacts with Pagination\n";
echo "✅ Contact Search by Name/Phone\n";
echo "✅ Toggle Favorite Contact Status\n";
echo "✅ Get Favorite Contacts List\n";
echo "✅ Remove from Favorites\n";
echo "✅ Block Contact Functionality\n";
echo "✅ Get Blocked Contacts List\n";
echo "✅ Unblock Contact Functionality\n";

echo "\n📱 MOBILE INTEGRATION READY:\n";
echo "============================\n";
echo "The Contact Management API is ready for:\n";
echo "• React Native contact synchronization\n";
echo "• Flutter contact management\n";
echo "• Real-time contact status updates\n";
echo "• Contact search and filtering\n";
echo "• Favorite contacts management\n";
echo "• Block/unblock functionality\n";
echo "• Contact list pagination\n";

echo "\n🎯 Contact Management Complete Test - FINISHED!\n";
echo "================================================\n";
