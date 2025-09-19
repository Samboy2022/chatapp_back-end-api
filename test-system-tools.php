<?php

/**
 * Test script for admin system tools functionality
 */

$baseUrl = 'http://127.0.0.1:8000';

echo "🔧 Testing Admin System Tools\n";
echo "=============================\n\n";

function makePostRequest($endpoint, $data = [], $timeout = 30) {
    global $baseUrl;

    $url = $baseUrl . $endpoint;
    echo "📞 Testing: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL Error: $error\n\n";
        return false;
    }
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 400) {
        // Check if response contains success message
        if (strpos($response, 'success') !== false || strpos($response, 'successfully') !== false) {
            echo "✅ Operation completed successfully\n\n";
            return true;
        } else if (strpos($response, 'error') !== false || strpos($response, 'failed') !== false) {
            echo "⚠️  Operation may have failed (check response)\n\n";
            return false;
        } else {
            echo "✅ Request completed (status {$httpCode})\n\n";
            return true;
        }
    } else {
        echo "❌ Error response (status {$httpCode})\n";
        echo substr($response, 0, 200) . "...\n\n";
        return false;
    }
}

function makeGetRequest($endpoint) {
    global $baseUrl;
    
    $url = $baseUrl . $endpoint;
    echo "📞 Testing: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL Error: $error\n\n";
        return false;
    }
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "✅ Request completed successfully\n\n";
        return true;
    } else {
        echo "❌ Error response (status {$httpCode})\n\n";
        return false;
    }
}

// Get CSRF token first
echo "🔐 Getting CSRF token...\n";
$settingsPage = file_get_contents($baseUrl . '/admin/settings');
if (preg_match('/name="csrf-token" content="([^"]+)"/', $settingsPage, $matches)) {
    $csrfToken = $matches[1];
    echo "✅ CSRF token obtained\n\n";
} else {
    echo "❌ Could not get CSRF token\n\n";
    $csrfToken = '';
}

// Test system tools endpoints using API routes (no CSRF protection)
$endpoints = [
    [
        'name' => 'Clear Cache',
        'endpoint' => '/api/admin/settings/clear-cache',
        'method' => 'POST',
        'data' => [],
        'timeout' => 30
    ],
    [
        'name' => 'Optimize System',
        'endpoint' => '/api/admin/settings/optimize',
        'method' => 'POST',
        'data' => [],
        'timeout' => 10 // Shorter timeout for optimize
    ],
    [
        'name' => 'Test Email',
        'endpoint' => '/api/admin/settings/test-email',
        'method' => 'POST',
        'data' => [],
        'timeout' => 30
    ],
    [
        'name' => 'Export Settings',
        'endpoint' => '/api/admin/settings/export',
        'method' => 'GET',
        'data' => [],
        'timeout' => 30
    ]
];

$results = [];

foreach ($endpoints as $test) {
    echo "🔧 Testing {$test['name']}...\n";

    if ($test['method'] === 'POST') {
        $timeout = $test['timeout'] ?? 30;
        $results[$test['name']] = makePostRequest($test['endpoint'], $test['data'], $timeout);
    } else {
        $results[$test['name']] = makeGetRequest($test['endpoint']);
    }
}

// Test backup database (might fail if mysqldump is not available)
echo "🔧 Testing Database Backup...\n";
$backupResult = makePostRequest('/api/admin/settings/backup', []);
if (!$backupResult) {
    echo "⚠️  Database backup may require mysqldump to be installed\n\n";
}
$results['Database Backup'] = $backupResult;

// Summary
echo "📋 SYSTEM TOOLS TEST SUMMARY\n";
echo "============================\n";
foreach ($results as $test => $success) {
    $status = $success ? '✅ PASS' : '❌ FAIL';
    echo "$status $test\n";
}

echo "\n🎯 System tools test completed!\n";

// Test direct Laravel commands
echo "\n🔧 Testing Direct Laravel Commands...\n";
echo "=====================================\n";

$commands = [
    'cache:clear' => 'Clear application cache',
    'config:clear' => 'Clear configuration cache',
    'route:clear' => 'Clear route cache',
    'view:clear' => 'Clear view cache'
];

foreach ($commands as $command => $description) {
    echo "📞 Testing: php artisan {$command} ({$description})\n";
    
    $output = shell_exec("cd c:\\laragon\\www\\chat-app-backend\\chatapp_back-end-api && php artisan {$command} 2>&1");
    
    if (strpos($output, 'successfully') !== false || strpos($output, 'cleared') !== false) {
        echo "✅ Command executed successfully\n";
    } else if (strpos($output, 'error') !== false || strpos($output, 'failed') !== false) {
        echo "❌ Command failed: " . trim($output) . "\n";
    } else {
        echo "✅ Command completed\n";
    }
    echo "\n";
}

echo "🎯 Direct command test completed!\n";
