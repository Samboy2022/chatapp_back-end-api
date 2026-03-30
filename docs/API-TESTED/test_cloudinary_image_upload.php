<?php

/**
 * Cloudinary Image Upload Test Script
 * 
 * This script tests image upload functionality to Cloudinary
 * 
 * Usage:
 * 1. Update the BASE_URL and credentials below
 * 2. Run: php test_cloudinary_image_upload.php
 */

// Configuration
$BASE_URL = 'http://localhost:8000'; // Update with your API URL
$TEST_EMAIL = 'test@example.com';
$TEST_PASSWORD = 'password';

// ANSI color codes for terminal output
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

// Test results tracking
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

/**
 * Print colored output
 */
function printColor($message, $color = '') {
    global $NC;
    echo $color . $message . $NC . PHP_EOL;
}

/**
 * Print test header
 */
function printTestHeader($testName) {
    global $BLUE;
    printColor("\n" . str_repeat('=', 70), $BLUE);
    printColor("TEST: $testName", $BLUE);
    printColor(str_repeat('=', 70), $BLUE);
}

/**
 * Print test result
 */
function printTestResult($passed, $message = '') {
    global $GREEN, $RED, $totalTests, $passedTests, $failedTests;
    
    $totalTests++;
    if ($passed) {
        $passedTests++;
        printColor("✓ PASS" . ($message ? ": $message" : ''), $GREEN);
    } else {
        $failedTests++;
        printColor("✗ FAIL" . ($message ? ": $message" : ''), $RED);
    }
}

/**
 * Make HTTP request
 */
function makeRequest($method, $endpoint, $data = null, $token = null, $isMultipart = false) {
    global $BASE_URL;
    
    $url = $BASE_URL . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Accept: application/json'];
    
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    if ($data) {
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
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
        return ['error' => $error, 'http_code' => $httpCode];
    }
    
    return [
        'http_code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

/**
 * Create a test image file
 */
function createTestImage($filename = 'test_image.jpg', $width = 800, $height = 600) {
    $image = imagecreatetruecolor($width, $height);
    
    // Fill with random colors
    $bgColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imagefill($image, 0, 0, $bgColor);
    
    // Add some text
    $textColor = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, 5, 10, 10, "Test Image " . date('Y-m-d H:i:s'), $textColor);
    
    // Save to file
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
    
    return $filename;
}

/**
 * Login and get token
 */
function login() {
    global $TEST_EMAIL, $TEST_PASSWORD;
    
    printTestHeader("Authentication - Login");
    
    $response = makeRequest('POST', '/api/auth/login', [
        'email' => $TEST_EMAIL,
        'password' => $TEST_PASSWORD
    ]);
    
    if ($response['http_code'] === 200 && isset($response['body']['data']['token'])) {
        printTestResult(true, "Login successful");
        return $response['body']['data']['token'];
    }
    
    // Try to register if login fails
    printColor("Login failed, attempting registration...", $YELLOW);
    
    $response = makeRequest('POST', '/api/auth/register', [
        'name' => 'Test User',
        'email' => $TEST_EMAIL,
        'password' => $TEST_PASSWORD,
        'password_confirmation' => $TEST_PASSWORD,
        'phone' => '+1234567890'
    ]);
    
    if ($response['http_code'] === 201 && isset($response['body']['data']['token'])) {
        printTestResult(true, "Registration successful");
        return $response['body']['data']['token'];
    }
    
    printTestResult(false, "Authentication failed");
    return null;
}

/**
 * Test 1: Upload image to Cloudinary
 */
function testUploadImage($token) {
    printTestHeader("Test 1: Upload Image to Cloudinary");
    
    // Create test image
    $imagePath = createTestImage('test_upload.jpg', 1024, 768);
    
    // Prepare multipart form data
    $cfile = new CURLFile($imagePath, 'image/jpeg', 'test_upload.jpg');
    $data = [
        'file' => $cfile,
        'type' => 'image'
    ];
    
    $response = makeRequest('POST', '/api/media/upload', $data, $token, true);
    
    // Clean up test file
    unlink($imagePath);
    
    // Verify response
    $passed = $response['http_code'] === 201 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true
        && isset($response['body']['data']['url'])
        && strpos($response['body']['data']['url'], 'cloudinary.com') !== false;
    
    printTestResult($passed, "Image uploaded to Cloudinary");
    
    if ($passed) {
        printColor("  URL: " . $response['body']['data']['url'], $GREEN);
        printColor("  Public ID: " . $response['body']['data']['public_id'], $GREEN);
        printColor("  Thumbnail: " . ($response['body']['data']['thumbnail_url'] ?? 'N/A'), $GREEN);
        return $response['body']['data'];
    } else {
        printColor("  Response: " . json_encode($response['body'], JSON_PRETTY_PRINT), $RED);
        return null;
    }
}

/**
 * Test 2: Upload avatar
 */
function testUploadAvatar($token) {
    printTestHeader("Test 2: Upload Avatar to Cloudinary");
    
    $imagePath = createTestImage('test_avatar.jpg', 500, 500);
    
    $cfile = new CURLFile($imagePath, 'image/jpeg', 'test_avatar.jpg');
    $data = ['avatar' => $cfile];
    
    $response = makeRequest('POST', '/api/media/upload-avatar', $data, $token, true);
    
    unlink($imagePath);
    
    $passed = $response['http_code'] === 201 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true
        && isset($response['body']['data']['avatar_url'])
        && isset($response['body']['data']['thumbnail_url'])
        && isset($response['body']['data']['small_url']);
    
    printTestResult($passed, "Avatar uploaded with multiple sizes");
    
    if ($passed) {
        printColor("  Avatar URL: " . $response['body']['data']['avatar_url'], $GREEN);
        printColor("  Thumbnail URL: " . $response['body']['data']['thumbnail_url'], $GREEN);
        printColor("  Small URL: " . $response['body']['data']['small_url'], $GREEN);
        return $response['body']['data'];
    } else {
        printColor("  Response: " . json_encode($response['body'], JSON_PRETTY_PRINT), $RED);
        return null;
    }
}

/**
 * Test 3: Upload status media
 */
function testUploadStatusMedia($token) {
    printTestHeader("Test 3: Upload Status Media to Cloudinary");
    
    $imagePath = createTestImage('test_status.jpg', 1080, 1920);
    
    $cfile = new CURLFile($imagePath, 'image/jpeg', 'test_status.jpg');
    $data = [
        'media' => $cfile,
        'type' => 'image'
    ];
    
    $response = makeRequest('POST', '/api/media/upload-status-media', $data, $token, true);
    
    unlink($imagePath);
    
    $passed = $response['http_code'] === 201 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true
        && isset($response['body']['data']['url']);
    
    printTestResult($passed, "Status media uploaded");
    
    if ($passed) {
        printColor("  URL: " . $response['body']['data']['url'], $GREEN);
        printColor("  Size: " . $response['body']['data']['size_formatted'], $GREEN);
        return $response['body']['data'];
    } else {
        printColor("  Response: " . json_encode($response['body'], JSON_PRETTY_PRINT), $RED);
        return null;
    }
}

/**
 * Test 4: Upload different image formats
 */
function testUploadDifferentFormats($token) {
    printTestHeader("Test 4: Upload Different Image Formats");
    
    $formats = [
        ['ext' => 'jpg', 'mime' => 'image/jpeg'],
        ['ext' => 'png', 'mime' => 'image/png'],
    ];
    
    foreach ($formats as $format) {
        $filename = "test_format.{$format['ext']}";
        
        // Create image based on format
        $image = imagecreatetruecolor(400, 300);
        $bgColor = imagecolorallocate($image, 100, 150, 200);
        imagefill($image, 0, 0, $bgColor);
        
        if ($format['ext'] === 'jpg') {
            imagejpeg($image, $filename, 90);
        } else {
            imagepng($image, $filename);
        }
        imagedestroy($image);
        
        $cfile = new CURLFile($filename, $format['mime'], $filename);
        $data = [
            'file' => $cfile,
            'type' => 'image'
        ];
        
        $response = makeRequest('POST', '/api/media/upload', $data, $token, true);
        
        unlink($filename);
        
        $passed = $response['http_code'] === 201 
            && isset($response['body']['success']) 
            && $response['body']['success'] === true;
        
        printTestResult($passed, "Uploaded {$format['ext']} format");
        
        if ($passed) {
            printColor("  Format: " . $response['body']['data']['format'], $GREEN);
        }
    }
}

/**
 * Test 5: Validation tests
 */
function testValidation($token) {
    printTestHeader("Test 5: Validation Tests");
    
    // Test missing file
    $response = makeRequest('POST', '/api/media/upload', ['type' => 'image'], $token);
    $passed = $response['http_code'] === 422;
    printTestResult($passed, "Validation: Missing file rejected");
    
    // Test missing type
    $imagePath = createTestImage('test_validation.jpg');
    $cfile = new CURLFile($imagePath, 'image/jpeg', 'test_validation.jpg');
    $response = makeRequest('POST', '/api/media/upload', ['file' => $cfile], $token, true);
    unlink($imagePath);
    $passed = $response['http_code'] === 422;
    printTestResult($passed, "Validation: Missing type rejected");
}

/**
 * Test 6: Get user media
 */
function testGetUserMedia($token) {
    printTestHeader("Test 6: Get User Media");
    
    $response = makeRequest('GET', '/api/media/user', null, $token);
    
    $passed = $response['http_code'] === 200 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true
        && isset($response['body']['data']);
    
    printTestResult($passed, "Retrieved user media");
    
    if ($passed) {
        printColor("  Total files: " . $response['body']['count'], $GREEN);
    }
}

/**
 * Test 7: Get media statistics
 */
function testGetMediaStats($token) {
    printTestHeader("Test 7: Get Media Statistics");
    
    $response = makeRequest('GET', '/api/media/stats', null, $token);
    
    $passed = $response['http_code'] === 200 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true
        && isset($response['body']['data']['total_files']);
    
    printTestResult($passed, "Retrieved media statistics");
    
    if ($passed) {
        $stats = $response['body']['data'];
        printColor("  Total files: " . $stats['total_files'], $GREEN);
        printColor("  Total size: " . $stats['total_size_formatted'], $GREEN);
        printColor("  Images: " . $stats['by_type']['images'], $GREEN);
    }
}

/**
 * Test 8: Delete media
 */
function testDeleteMedia($token, $uploadedMedia) {
    printTestHeader("Test 8: Delete Media from Cloudinary");
    
    if (!$uploadedMedia || !isset($uploadedMedia['public_id'])) {
        printTestResult(false, "No media to delete");
        return;
    }
    
    $response = makeRequest('POST', '/api/media/delete', [
        'public_id' => $uploadedMedia['public_id'],
        'resource_type' => 'image'
    ], $token);
    
    $passed = $response['http_code'] === 200 
        && isset($response['body']['success']) 
        && $response['body']['success'] === true;
    
    printTestResult($passed, "Media deleted from Cloudinary");
}

/**
 * Main test execution
 */
function runTests() {
    global $totalTests, $passedTests, $failedTests, $GREEN, $RED, $YELLOW, $BLUE;
    
    printColor("\n" . str_repeat('=', 70), $BLUE);
    printColor("CLOUDINARY IMAGE UPLOAD TEST SUITE", $BLUE);
    printColor(str_repeat('=', 70) . "\n", $BLUE);
    
    // Login
    $token = login();
    if (!$token) {
        printColor("Cannot proceed without authentication token", $RED);
        return;
    }
    
    // Run tests
    $uploadedMedia = testUploadImage($token);
    $uploadedAvatar = testUploadAvatar($token);
    $uploadedStatus = testUploadStatusMedia($token);
    testUploadDifferentFormats($token);
    testValidation($token);
    testGetUserMedia($token);
    testGetMediaStats($token);
    testDeleteMedia($token, $uploadedMedia);
    
    // Print summary
    printColor("\n" . str_repeat('=', 70), $BLUE);
    printColor("TEST SUMMARY", $BLUE);
    printColor(str_repeat('=', 70), $BLUE);
    printColor("Total Tests: $totalTests", $YELLOW);
    printColor("Passed: $passedTests", $GREEN);
    printColor("Failed: $failedTests", $RED);
    
    $percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
    printColor("Success Rate: {$percentage}%", $percentage >= 80 ? $GREEN : $RED);
    printColor(str_repeat('=', 70) . "\n", $BLUE);
}

// Run the tests
runTests();
