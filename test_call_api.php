<?php
/**
 * Test script for the Call API.
 *
 * Usage: php test_call_api.php
 *
 * This script tests:
 * 1. User registration & login
 * 2. FCM token update
 * 3. Call initiation with validation
 * 4. Agora token generation
 * 5. Call status tracking
 * 6. Call end
 * 7. Validation edge cases (missing/invalid receiver_id)
 *
 * NOTE: Real FCM delivery requires a valid Firebase device token from a real device.
 *       The FCM send will fail with fake tokens, but the API logic is verified.
 */

$baseUrl = 'http://127.0.0.1:8000/api';
$verbose = true;

// ─── Helper Functions ───────────────────────────────────────────────────────

function request(string $method, string $url, array $data = [], ?string $token = null): array
{
    global $verbose;

    $ch = curl_init($url);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($method !== 'GET' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $decoded = json_decode($response, true) ?? ['raw_response' => $response];

    if ($verbose) {
        echo "\n📡 $method $url\n";
        echo "   HTTP $httpCode\n";
        if ($error) echo "   CURL ERROR: $error\n";
        echo "   Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    }

    return ['code' => $httpCode, 'data' => $decoded, 'error' => $error];
}

function success(string $msg) { echo "\n✅ $msg\n"; }
function error(string $msg)   { echo "\n❌ $msg\n"; exit(1); }
function info(string $msg)    { echo "\nℹ️  $msg\n"; }
function section(string $msg) { echo "\n" . str_repeat('=', 60) . "\n📋 $msg\n" . str_repeat('=', 60) . "\n"; }
function warn(string $msg)    { echo "\n⚠️  $msg\n"; }

// ─── Test Execution ─────────────────────────────────────────────────────────

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     FarmersNetwork Call API Test Script                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

$timestamp = time();
$callerEmail = "caller_a_{$timestamp}@test.com";
$callerPhone = "+234801{$timestamp}111";
$receiverEmail = "receiver_b_{$timestamp}@test.com";
$receiverPhone = "+234802{$timestamp}222";
$password = 'TestPassword123!';

// ─── Step 1: Register Caller A ──────────────────────────────────────────────
section("STEP 1: Register Caller A");
$regA = request('POST', "$baseUrl/auth/register", [
    'name' => 'Caller A',
    'email' => $callerEmail,
    'phone_number' => $callerPhone,
    'country_code' => '+234',
    'password' => $password,
    'password_confirmation' => $password,
]);

if ($regA['code'] !== 201 && !isset($regA['data']['data']['token'])) {
    info("Registration failed or user exists, trying login...");
    $loginA = request('POST', "$baseUrl/auth/login", [
        'login' => $callerEmail,
        'password' => $password,
    ]);
    if ($loginA['code'] !== 200 || !isset($loginA['data']['data']['token'])) {
        error("Failed to register or login Caller A");
    }
    $callerToken = $loginA['data']['data']['token'];
    $callerId = $loginA['data']['data']['user']['id'] ?? null;
} else {
    $callerToken = $regA['data']['data']['token'];
    $callerId = $regA['data']['data']['user']['id'] ?? null;
}
success("Caller A authenticated — ID: $callerId");

// ─── Step 2: Register Receiver B ────────────────────────────────────────────
section("STEP 2: Register Receiver B");
$regB = request('POST', "$baseUrl/auth/register", [
    'name' => 'Receiver B',
    'email' => $receiverEmail,
    'phone_number' => $receiverPhone,
    'country_code' => '+234',
    'password' => $password,
    'password_confirmation' => $password,
]);

if ($regB['code'] !== 201 && !isset($regB['data']['data']['token'])) {
    info("Registration failed or user exists, trying login...");
    $loginB = request('POST', "$baseUrl/auth/login", [
        'login' => $receiverEmail,
        'password' => $password,
    ]);
    if ($loginB['code'] !== 200 || !isset($loginB['data']['data']['token'])) {
        error("Failed to register or login Receiver B");
    }
    $receiverToken = $loginB['data']['data']['token'];
    $receiverId = $loginB['data']['data']['user']['id'] ?? null;
} else {
    $receiverToken = $regB['data']['data']['token'];
    $receiverId = $regB['data']['data']['user']['id'] ?? null;
}
success("Receiver B authenticated — ID: $receiverId");

// ─── Step 3: Update FCM Tokens ──────────────────────────────────────────────
section("STEP 3: Update FCM Tokens");

// Use fake tokens (real FCM requires actual device tokens from Firebase)
$callerFcmToken = 'caller_fcm_token_' . bin2hex(random_bytes(16));
$receiverFcmToken = 'receiver_fcm_token_' . bin2hex(random_bytes(16));

$fcma = request('POST', "$baseUrl/auth/fcm-token", [
    'fcm_token' => $callerFcmToken,
    'device_type' => 'android',
], $callerToken);

if ($fcma['code'] !== 200) {
    error("Failed to update Caller A FCM token");
}
success("Caller A FCM token updated");

$fcmb = request('POST', "$baseUrl/auth/fcm-token", [
    'fcm_token' => $receiverFcmToken,
    'device_type' => 'android',
], $receiverToken);

if ($fcmb['code'] !== 200) {
    error("Failed to update Receiver B FCM token");
}
success("Receiver B FCM token updated");

// ─── Step 4: Test FCM Notification Endpoint ─────────────────────────────────
section("STEP 4: Test FCM Notification Endpoint (Receiver B)");
$testFcm = request('POST', "$baseUrl/auth/test-fcm-notification", [], $receiverToken);

if ($testFcm['code'] === 200 && ($testFcm['data']['success'] ?? false)) {
    success("Test FCM notification sent to Receiver B!");
    info("Check Receiver B's device for a CallKit incoming call UI.");
} elseif ($testFcm['code'] === 500 && strpos($testFcm['data']['message'] ?? '', 'FCM service failed') !== false) {
    warn("FCM send failed — this is EXPECTED with a fake token.");
    info("The FCM service is working correctly; it just rejected the invalid token.");
    info("To test real delivery, use a real device token from Firebase.");
} elseif ($testFcm['code'] === 400) {
    error("No FCM token found for Receiver B — FCM update may have failed");
} else {
    error("FCM test failed: " . ($testFcm['data']['message'] ?? 'Unknown error'));
}

// ─── Step 5: Initiate Call from A to B ──────────────────────────────────────
section("STEP 5: Initiate Call from Caller A to Receiver B");
$initiate = request('POST', "$baseUrl/calls", [
    'receiver_id' => $receiverId,
    'type' => 'video',
], $callerToken);

if ($initiate['code'] !== 201) {
    error("Failed to initiate call: " . ($initiate['data']['message'] ?? 'Unknown'));
}

$callData = $initiate['data']['data'] ?? null;
$callId = $callData['id'] ?? null;
$agoraTokens = $callData['agora_tokens'] ?? null;

if (!$callId) {
    error("Call initiated but no call ID returned");
}
success("Call initiated — Call ID: $callId");

// ─── Step 6: Verify Agora Tokens ────────────────────────────────────────────
section("STEP 6: Verify Agora Tokens in Response");
if (!$agoraTokens) {
    error("No Agora tokens in response — token generation may have failed");
}

$requiredKeys = ['caller_token', 'receiver_token', 'channel', 'app_id', 'caller_uid', 'receiver_uid'];
$missing = array_diff($requiredKeys, array_keys($agoraTokens));
if (!empty($missing)) {
    error("Missing Agora token fields: " . implode(', ', $missing));
}

success("Agora tokens present:");
echo "   • caller_token:  " . substr($agoraTokens['caller_token'], 0, 30) . "...\n";
echo "   • receiver_token: " . substr($agoraTokens['receiver_token'], 0, 30) . "...\n";
echo "   • channel:        {$agoraTokens['channel']}\n";
echo "   • app_id:         {$agoraTokens['app_id']}\n";
echo "   • caller_uid:     {$agoraTokens['caller_uid']}\n";
echo "   • receiver_uid:   {$agoraTokens['receiver_uid']}\n";

// ─── Step 7: Verify Call Status ─────────────────────────────────────────────
section("STEP 7: Verify Call Status is 'ringing'");
$callStatus = request('GET', "$baseUrl/calls/$callId", [], $callerToken);
if ($callStatus['code'] === 200) {
    $status = $callStatus['data']['data']['status'] ?? 'unknown';
    if ($status === 'ringing') {
        success("Call status: $status ✓");
    } else {
        error("Expected status 'ringing', got '$status'");
    }
} else {
    error("Failed to get call status");
}

// ─── Step 8: Get Agora Tokens via Dedicated Endpoint ────────────────────────
section("STEP 8: Get Agora Tokens via /calls/{id}/agora-tokens");
$tokenEndpoint = request('GET', "$baseUrl/calls/$callId/agora-tokens", [], $callerToken);
if ($tokenEndpoint['code'] === 200) {
    $tokenData = $tokenEndpoint['data']['data'] ?? null;
    if ($tokenData) {
        success("Agora tokens retrieved from dedicated endpoint:");
        echo "   • caller_uid:   " . ($tokenData['caller_uid'] ?? 'N/A') . "\n";
        echo "   • receiver_uid: " . ($tokenData['receiver_uid'] ?? 'N/A') . "\n";
        // Verify UIDs match
        if (($tokenData['caller_uid'] ?? null) == $callerId && ($tokenData['receiver_uid'] ?? null) == $receiverId) {
            success("UIDs match the caller/receiver IDs ✓");
        } else {
            error("UID mismatch! Expected caller=$callerId, receiver=$receiverId");
        }
    }
} else {
    error("Failed to get Agora tokens from dedicated endpoint");
}

// ─── Step 9: Receiver B Answers the Call ────────────────────────────────────
section("STEP 9: Receiver B Answers the Call");
$answer = request('POST', "$baseUrl/calls/$callId/accept", [], $receiverToken);
if ($answer['code'] === 200) {
    success("Call answered successfully by Receiver B");
} else {
    error("Failed to answer call: " . ($answer['data']['message'] ?? 'Unknown'));
}

// ─── Step 10: Verify Call Status is 'answered' ──────────────────────────────
section("STEP 10: Verify Call Status is 'answered'");
$answeredStatus = request('GET', "$baseUrl/calls/$callId", [], $callerToken);
if ($answeredStatus['code'] === 200) {
    $status = $answeredStatus['data']['data']['status'] ?? 'unknown';
    if ($status === 'answered') {
        success("Call status: $status ✓");
    } else {
        error("Expected status 'answered', got '$status'");
    }
} else {
    error("Failed to get call status after answer");
}

// ─── Step 11: End the Call ──────────────────────────────────────────────────
section("STEP 11: End the Call");
$endCall = request('POST', "$baseUrl/calls/$callId/end", [], $callerToken);
if ($endCall['code'] === 200) {
    success("Call ended successfully");
} else {
    error("Failed to end call: " . ($endCall['data']['message'] ?? 'Unknown'));
}

// ─── Step 12: Verify Call Status is 'ended' ─────────────────────────────────
section("STEP 12: Verify Call Status is 'ended'");
$endedStatus = request('GET', "$baseUrl/calls/$callId", [], $callerToken);
if ($endedStatus['code'] === 200) {
    $status = $endedStatus['data']['data']['status'] ?? 'unknown';
    if ($status === 'ended') {
        success("Call status: $status ✓");
    } else {
        error("Expected status 'ended', got '$status'");
    }
} else {
    error("Failed to get call status after end");
}

// ─── Step 13: Validation Test — Missing receiver_id ─────────────────────────
section("STEP 13: Validation Test — Missing receiver_id");
$badCall = request('POST', "$baseUrl/calls", [
    'type' => 'video',
    // receiver_id intentionally missing
], $callerToken);

if ($badCall['code'] === 422) {
    success("Validation working — correctly rejected call without receiver_id (HTTP 422) ✓");
} else {
    error("Validation NOT working — expected 422, got {$badCall['code']}");
}

// ─── Step 14: Validation Test — Invalid receiver_id ─────────────────────────
section("STEP 14: Validation Test — Invalid receiver_id (non-existent user)");
$badCall2 = request('POST', "$baseUrl/calls", [
    'type' => 'video',
    'receiver_id' => 99999999, // Non-existent user
], $callerToken);

if ($badCall2['code'] === 422) {
    success("Validation working — correctly rejected call with invalid receiver_id (HTTP 422) ✓");
} else {
    error("Validation NOT working — expected 422, got {$badCall2['code']}");
}

// ─── Step 15: Validation Test — Calling self ────────────────────────────────
section("STEP 15: Validation Test — Calling self");
$selfCall = request('POST', "$baseUrl/calls", [
    'type' => 'video',
    'receiver_id' => $callerId, // Caller calling themselves
], $callerToken);

if ($selfCall['code'] === 400) {
    success("Self-call blocked — correctly rejected (HTTP 400) ✓");
} else {
    error("Self-call validation NOT working — expected 400, got {$selfCall['code']}");
}

// ─── Step 16: Call History ──────────────────────────────────────────────────
section("STEP 16: Verify Call History");
$history = request('GET', "$baseUrl/calls", [], $callerToken);
if ($history['code'] === 200) {
    $calls = $history['data']['data']['data'] ?? [];
    $found = false;
    foreach ($calls as $call) {
        if (($call['id'] ?? null) == $callId) {
            $found = true;
            break;
        }
    }
    if ($found) {
        success("Call history contains the test call ✓");
    } else {
        error("Call history does NOT contain the test call");
    }
} else {
    error("Failed to get call history");
}

// ─── Summary ────────────────────────────────────────────────────────────────
section("TEST SUMMARY");
echo "✅ Caller A ID:        $callerId\n";
echo "✅ Receiver B ID:      $receiverId\n";
echo "✅ Call ID:            $callId\n";
echo "✅ FCM Token (Caller):  " . substr($callerFcmToken, 0, 30) . "...\n";
echo "✅ FCM Token (Receiver):" . substr($receiverFcmToken, 0, 30) . "...\n";
echo "\n📌 FCM Delivery Note:\n";
echo "   The FCM send failed because we used a FAKE token.\n";
echo "   This is EXPECTED behavior.\n";
echo "   To test real FCM delivery, use a real device token from Firebase.\n";
echo "\n📌 Check Laravel logs at: storage/logs/laravel.log\n";
echo "   Look for lines containing:\n";
echo "   • 'FCM sent to receiver' → FCM was sent successfully\n";
echo "   • 'FCM send failed' → Token was stale and cleared\n";
echo "   • 'No FCM token for receiver' → Token was never registered\n";
echo "   • 'Receiver not found' → receiver_id was invalid\n";
echo "\n🎉 All API tests passed!\n\n";
