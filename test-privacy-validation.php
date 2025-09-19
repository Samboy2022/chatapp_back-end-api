<?php

/**
 * Test privacy settings validation specifically
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "🔧 Testing Privacy Settings Validation\n";
echo "======================================\n\n";

// Generate unique credentials for testing
$uniqueId = rand(100, 999);
$testUser = [
    'phone_number' => '+1234567' . $uniqueId,
    'password' => 'testpassword123',
    'email' => 'testuser' . $uniqueId . '@example.com',
    'name' => 'Test User ' . $uniqueId
];

function makeRequest($method, $url, $data = [], $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Status: $httpCode\n";
    echo "Response: $response\n\n";
    
    if ($error) {
        echo "cURL Error: $error\n";
        return false;
    }
    
    return json_decode($response, true);
}

// Step 1: Register and get token
echo "1. 🔐 Registering test user...\n";
$registerData = [
    'name' => $testUser['name'],
    'email' => $testUser['email'],
    'phone_number' => $testUser['phone_number'],
    'country_code' => '+1',
    'password' => $testUser['password'],
    'password_confirmation' => $testUser['password']
];

$registerResult = makeRequest('POST', $baseUrl . '/auth/register', $registerData);

if (!$registerResult || !isset($registerResult['data']['token'])) {
    echo "❌ Registration failed, cannot proceed with tests\n";
    exit(1);
}

$authToken = $registerResult['data']['token'];
echo "✅ Registration successful, token obtained\n\n";

// Step 2: Test valid privacy settings first
echo "2. ✅ Testing Valid Privacy Settings...\n";
$validPrivacyData = [
    'last_seen_privacy' => 'contacts',
    'profile_photo_privacy' => 'everyone',
    'about_privacy' => 'nobody',
    'read_receipts_enabled' => true
];

$validResult = makeRequest('POST', $baseUrl . '/settings/privacy', $validPrivacyData, $authToken);

if ($validResult && $validResult['success']) {
    echo "✅ Valid privacy settings accepted correctly\n\n";
} else {
    echo "❌ Valid privacy settings were rejected - this is a problem\n\n";
}

// Step 3: Test invalid enum values
echo "3. ❌ Testing Invalid Enum Values...\n";
$invalidEnumData = [
    'last_seen_privacy' => 'invalid_option',
    'profile_photo_privacy' => 'wrong_value',
    'about_privacy' => 'bad_choice'
];

$invalidEnumResult = makeRequest('POST', $baseUrl . '/settings/privacy', $invalidEnumData, $authToken);

if (!$invalidEnumResult || !$invalidEnumResult['success']) {
    echo "✅ Invalid enum values correctly rejected\n\n";
} else {
    echo "❌ Invalid enum values were accepted - validation failed\n\n";
}

// Step 4: Test invalid boolean
echo "4. ❌ Testing Invalid Boolean...\n";
$invalidBooleanData = [
    'read_receipts_enabled' => 'not_a_boolean'
];

$invalidBooleanResult = makeRequest('POST', $baseUrl . '/settings/privacy', $invalidBooleanData, $authToken);

if (!$invalidBooleanResult || !$invalidBooleanResult['success']) {
    echo "✅ Invalid boolean correctly rejected\n\n";
} else {
    echo "❌ Invalid boolean was accepted - validation failed\n\n";
}

// Step 5: Test unknown fields
echo "5. ❌ Testing Unknown Fields...\n";
$unknownFieldsData = [
    'unknown_field' => 'some_value',
    'another_unknown' => 'another_value',
    'last_seen_privacy' => 'everyone' // This is valid
];

$unknownFieldsResult = makeRequest('POST', $baseUrl . '/settings/privacy', $unknownFieldsData, $authToken);

if (!$unknownFieldsResult || !$unknownFieldsResult['success']) {
    echo "✅ Unknown fields correctly rejected\n\n";
} else {
    echo "❌ Unknown fields were accepted - validation failed\n\n";
}

// Step 6: Test mixed valid and invalid data
echo "6. ❌ Testing Mixed Valid and Invalid Data...\n";
$mixedData = [
    'last_seen_privacy' => 'contacts', // Valid
    'profile_photo_privacy' => 'invalid_value', // Invalid
    'read_receipts_enabled' => true // Valid
];

$mixedResult = makeRequest('POST', $baseUrl . '/settings/privacy', $mixedData, $authToken);

if (!$mixedResult || !$mixedResult['success']) {
    echo "✅ Mixed data with invalid values correctly rejected\n\n";
} else {
    echo "❌ Mixed data with invalid values was accepted - validation failed\n\n";
}

echo "🎯 Privacy Settings Validation Test Complete!\n";
echo "=============================================\n";
echo "This test verifies that the privacy settings endpoint properly validates:\n";
echo "• Enum values (everyone, contacts, nobody)\n";
echo "• Boolean values (true/false)\n";
echo "• Unknown fields rejection\n";
echo "• Mixed valid/invalid data handling\n";
