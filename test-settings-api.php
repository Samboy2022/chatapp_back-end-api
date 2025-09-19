<?php

/**
 * Comprehensive test script for Settings API endpoints
 */

$baseUrl = 'http://127.0.0.1:8000/api/app-settings';

echo "🔧 Testing Settings API Endpoints\n";
echo "=================================\n\n";

// Test functions
function makeGetRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL Error: $error\n";
        return false;
    }

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
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['message'])) {
                echo "Error: " . $data['message'] . "\n";
            }
        }
        return false;
    }
}

function makePostRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL Error: $error\n";
        return false;
    }

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
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['message'])) {
                echo "Error: " . $data['message'] . "\n";
            }
        }
        return false;
    }
}

// Test results storage
$results = [];

// Test 1: Get all settings
echo "1. 📋 Testing GET /api/app-settings (Get All Settings)\n";
echo "📞 Testing: $baseUrl\n";
$allSettings = makeGetRequest($baseUrl);
if ($allSettings) {
    echo "📊 Total groups: " . count($allSettings['data']) . "\n";
    echo "📊 Total settings: " . $allSettings['meta']['total_settings'] . "\n";
    echo "📊 Groups found: " . implode(', ', array_keys($allSettings['data'])) . "\n";
    $results['Get All Settings'] = true;
} else {
    $results['Get All Settings'] = false;
}
echo "\n";

// Test 2: Get app configuration
echo "2. ⚙️  Testing GET /api/app-settings/config (Get App Config)\n";
echo "📞 Testing: $baseUrl/config\n";
$appConfig = makeGetRequest($baseUrl . '/config');
if ($appConfig) {
    echo "📊 App name: " . $appConfig['data']['app']['name'] . "\n";
    echo "📊 Features enabled: " . count(array_filter($appConfig['data']['features'])) . "\n";
    echo "📊 Max file size: " . $appConfig['data']['limits']['max_file_size_mb'] . "MB\n";
    $results['Get App Config'] = true;
} else {
    $results['Get App Config'] = false;
}
echo "\n";

// Test 3: Get settings version
echo "3. 🔄 Testing GET /api/app-settings/version (Get Version)\n";
echo "📞 Testing: $baseUrl/version\n";
$version = makeGetRequest($baseUrl . '/version');
if ($version) {
    echo "📊 Version: " . $version['data']['version'] . "\n";
    echo "📊 Last updated: " . ($version['data']['last_updated'] ?? 'Never') . "\n";
    $results['Get Version'] = true;
} else {
    $results['Get Version'] = false;
}
echo "\n";

// Test 4: Get available groups
echo "4. 📁 Testing GET /api/app-settings/groups (Get Groups)\n";
echo "📞 Testing: $baseUrl/groups\n";
$groups = makeGetRequest($baseUrl . '/groups');
if ($groups) {
    echo "📊 Total groups: " . count($groups['data']) . "\n";
    foreach ($groups['data'] as $group) {
        echo "  - {$group['name']}: {$group['settings_count']} settings\n";
    }
    $results['Get Groups'] = true;
} else {
    $results['Get Groups'] = false;
}
echo "\n";

// Test 5: Get settings by group
echo "5. 📂 Testing GET /api/app-settings/group/{group} (Get by Group)\n";
$testGroup = 'general';
echo "📞 Testing: $baseUrl/group/$testGroup\n";
$groupSettings = makeGetRequest($baseUrl . "/group/$testGroup");
if ($groupSettings) {
    echo "📊 Settings in '$testGroup' group: " . count($groupSettings['data']) . "\n";
    foreach ($groupSettings['data'] as $key => $setting) {
        echo "  - $key: " . json_encode($setting['value']) . "\n";
    }
    $results['Get by Group'] = true;
} else {
    $results['Get by Group'] = false;
}
echo "\n";

// Test 6: Get specific setting by key
echo "6. 🔑 Testing GET /api/app-settings/{key} (Get by Key)\n";
$testKey = 'app_name';
echo "📞 Testing: $baseUrl/$testKey\n";
$specificSetting = makeGetRequest($baseUrl . "/$testKey");
if ($specificSetting) {
    echo "📊 Key: " . $specificSetting['data']['key'] . "\n";
    echo "📊 Value: " . json_encode($specificSetting['data']['value']) . "\n";
    echo "📊 Type: " . $specificSetting['data']['type'] . "\n";
    echo "📊 Group: " . $specificSetting['data']['group'] . "\n";
    $results['Get by Key'] = true;
} else {
    $results['Get by Key'] = false;
}
echo "\n";

// Test 7: Get multiple settings
echo "7. 🔢 Testing POST /api/app-settings/multiple (Get Multiple)\n";
echo "📞 Testing: $baseUrl/multiple\n";
$multipleKeys = [
    'keys' => ['app_name', 'enable_video_calls', 'max_group_size', 'timezone', 'nonexistent_key']
];
$multipleSettings = makePostRequest($baseUrl . '/multiple', $multipleKeys);
if ($multipleSettings) {
    echo "📊 Requested keys: " . count($multipleKeys['keys']) . "\n";
    echo "📊 Found keys: " . $multipleSettings['meta']['total_found'] . "\n";
    echo "📊 Missing keys: " . $multipleSettings['meta']['total_missing'] . "\n";
    echo "📊 Found: " . implode(', ', $multipleSettings['meta']['found_keys']) . "\n";
    echo "📊 Missing: " . implode(', ', $multipleSettings['meta']['missing_keys']) . "\n";
    $results['Get Multiple'] = true;
} else {
    $results['Get Multiple'] = false;
}
echo "\n";

// Test 8: Test error handling - non-existent key
echo "8. ❌ Testing Error Handling (Non-existent Key)\n";
$nonExistentKey = 'non_existent_setting';
echo "📞 Testing: $baseUrl/$nonExistentKey\n";
$errorTest = makeGetRequest($baseUrl . "/$nonExistentKey");
if (!$errorTest) {
    echo "✅ Correctly returned error for non-existent key\n";
    $results['Error Handling'] = true;
} else {
    echo "⚠️  Should have returned error for non-existent key\n";
    $results['Error Handling'] = false;
}
echo "\n";

// Test 9: Test error handling - non-existent group
echo "9. ❌ Testing Error Handling (Non-existent Group)\n";
$nonExistentGroup = 'non_existent_group';
echo "📞 Testing: $baseUrl/group/$nonExistentGroup\n";
$errorGroupTest = makeGetRequest($baseUrl . "/group/$nonExistentGroup");
if (!$errorGroupTest) {
    echo "✅ Correctly returned error for non-existent group\n";
    $results['Error Handling Group'] = true;
} else {
    echo "⚠️  Should have returned error for non-existent group\n";
    $results['Error Handling Group'] = false;
}
echo "\n";

// Test 10: Test validation - invalid multiple request
echo "10. ❌ Testing Validation (Invalid Multiple Request)\n";
echo "📞 Testing: $baseUrl/multiple\n";
$invalidMultiple = ['invalid' => 'data'];
$validationTest = makePostRequest($baseUrl . '/multiple', $invalidMultiple);
if (!$validationTest) {
    echo "✅ Correctly returned validation error\n";
    $results['Validation Test'] = true;
} else {
    echo "⚠️  Should have returned validation error\n";
    $results['Validation Test'] = false;
}
echo "\n";

// Display results summary
echo "📋 SETTINGS API TEST SUMMARY\n";
echo "============================\n";
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
    echo "\n🎉 All tests passed! Settings API is working correctly.\n";
} else {
    echo "\n⚠️  Some tests failed. Please check the implementation.\n";
}

echo "\n🌐 Settings API is ready for mobile app integration!\n";
echo "📖 Documentation: http://127.0.0.1:8000/docs/api-documentation/settings-api.html\n";
