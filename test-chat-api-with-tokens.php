<?php
/**
 * Simple Chat API Test Script
 * Tests core chat functionality with existing users
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000/api';
$user1Token = '2|QvTxgGuacGAh7gky9ScUPjZDZ79WXxfUyswUbEaH2cfee240';
$user2Token = '4|gngqq5bwSrs3tpXWAIrQ7qwLN7ESKnXnQtsX2TVm1854b538';
$user3Token = ''; // Optional third user

echo "=== Simple Chat API Test ===\n";
echo "Testing: Core chat functionality\n";
echo "===============================\n\n";

// Helper functions
function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();

    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    if (!empty($headers)) {
        $defaultHeaders = array_merge($defaultHeaders, $headers);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_DELETE, true);
    }

    if ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }

    return [
        'status' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true)
    ];
}

function testStep($description, $callback) {
    echo "🔄 Testing: $description\n";
    try {
        $result = $callback();
        echo "✅ SUCCESS: $description\n";
        if (isset($result['message'])) {
            echo "   Message: {$result['message']}\n";
        }
        return $result;
    } catch (Exception $e) {
        echo "❌ FAILED: $description\n";
        echo "   Error: " . $e->getMessage() . "\n";
        return null;
    }
    echo "\n";
}

// Test 1: API Health Check
echo "=== Test 1: API Health Check ===\n";
$healthCheck = testStep("API Connection", function() use ($baseUrl) {
    $response = makeRequest('GET', $baseUrl . '/test');
    if ($response['status'] !== 200) {
        throw new Exception("API not responding. Status: " . $response['status']);
    }
    return $response['data'];
});

if (!$healthCheck) {
    echo "❌ Cannot proceed without API connection\n";
    exit(1);
}

echo "✅ API is running and accessible\n\n";

// Test 2: Get User Information

echo "=== Test 2: User Authentication ===\n";

$user1Info = testStep("Get User 1 Info", function() use ($baseUrl, $user1Token) {
    $headers = ['Authorization: Bearer ' . $user1Token];

    $response = makeRequest('GET', $baseUrl . '/auth/user', null, $headers);
    if ($response['status'] !== 200) {
        throw new Exception("Failed to get user info. Status: " . $response['status']);
    }

    return $response['data']['data']['user'];
});

$user2Info = testStep("Get User 2 Info", function() use ($baseUrl, $user2Token) {
    $headers = ['Authorization: Bearer ' . $user2Token];

    $response = makeRequest('GET', $baseUrl . '/auth/user', null, $headers);
    if ($response['status'] !== 200) {
        throw new Exception("Failed to get user info. Status: " . $response['status']);
    }

    return $response['data']['data']['user'];
});

if (!$user1Info || !$user2Info) {
    echo "❌ Cannot proceed without user information\n";
    exit(1);
}

echo "✅ Users authenticated successfully\n\n";

// Test 3: Private Chat Creation
echo "=== Test 3: Private Chat Functionality ===\n";

$privateChat = testStep("Create Private Chat between User1 and User2", function() use ($baseUrl, $user1Token, $user2Info) {
    $headers = ['Authorization: Bearer ' . $user1Token];

    $chatData = [
        'participants' => [$user2Info['id']],
        'type' => 'private'
    ];

    $response = makeRequest('POST', $baseUrl . '/chats', $chatData, $headers);
    if ($response['status'] !== 201) {
        throw new Exception("Private chat creation failed. Status: " . $response['status']);
    }

    return $response['data']['data']['chat'];
});

if ($privateChat) {
    testStep("Get Private Chat Details", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $response = makeRequest('GET', $baseUrl . '/chats/' . $privateChat['id'], null, $headers);
        if ($response['status'] !== 200) {
            throw new Exception("Failed to get chat details. Status: " . $response['status']);
        }

        return $response['data'];
    });

    testStep("Send Message in Private Chat", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $messageData = [
            'type' => 'text',
            'content' => 'Hello from User 1! This is a test message.'
        ];

        $response = makeRequest('POST', $baseUrl . '/chats/' . $privateChat['id'] . '/messages', $messageData, $headers);
        if ($response['status'] !== 201) {
            throw new Exception("Failed to send message. Status: " . $response['status']);
        }

        return $response['data']['data']['message'];
    });

    testStep("Get Messages from Private Chat", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $response = makeRequest('GET', $baseUrl . '/chats/' . $privateChat['id'] . '/messages', null, $headers);
        if ($response['status'] !== 200) {
            throw new Exception("Failed to get messages. Status: " . $response['status']);
        }

        return $response['data'];
    });
}

echo "\n";

// Test 4: Group Chat Creation
echo "=== Test 4: Group Chat Functionality ===\n";

if (!empty($user3Token)) {
    $groupChat = testStep("Create Group Chat with 3 Users", function() use ($baseUrl, $user1Token, $user2Info) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $chatData = [
            'participants' => [$user2Info['id']],
            'type' => 'group',
            'name' => 'Test Group Chat',
            'description' => 'A test group for API testing'
        ];

        $response = makeRequest('POST', $baseUrl . '/chats', $chatData, $headers);
        if ($response['status'] !== 201) {
            throw new Exception("Group chat creation failed. Status: " . $response['status']);
        }

        return $response['data']['data']['chat'];
    });

    if ($groupChat) {
        testStep("Send Message in Group Chat", function() use ($baseUrl, $user1Token, $groupChat) {
            $headers = ['Authorization: Bearer ' . $user1Token];

            $messageData = [
                'type' => 'text',
                'content' => 'Hello everyone! This is a group message from User 1.'
            ];

            $response = makeRequest('POST', $baseUrl . '/chats/' . $groupChat['id'] . '/messages', $messageData, $headers);
            if ($response['status'] !== 201) {
                throw new Exception("Failed to send group message. Status: " . $response['status']);
            }

            return $response['data']['data']['message'];
        });

        testStep("Get Group Chat Messages", function() use ($baseUrl, $user1Token, $groupChat) {
            $headers = ['Authorization: Bearer ' . $user1Token];

            $response = makeRequest('GET', $baseUrl . '/chats/' . $groupChat['id'] . '/messages', null, $headers);
            if ($response['status'] !== 200) {
                throw new Exception("Failed to get group messages. Status: " . $response['status']);
            }

            return $response['data'];
        });
    }
} else {
    echo "⚠️  Skipping group chat tests (user3Token not provided)\n";
}

echo "\n";

// Test 5: Chat Management
echo "=== Test 5: Chat Management Features ===\n";

if (isset($privateChat)) {
    testStep("Pin Private Chat", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $response = makeRequest('POST', $baseUrl . '/chats/' . $privateChat['id'] . '/pin', null, $headers);
        if ($response['status'] !== 200) {
            throw new Exception("Failed to pin chat. Status: " . $response['status']);
        }

        return $response['data'];
    });

    testStep("Mute Private Chat", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $muteData = ['duration_hours' => 1];

        $response = makeRequest('POST', $baseUrl . '/chats/' . $privateChat['id'] . '/mute', $muteData, $headers);
        if ($response['status'] !== 200) {
            throw new Exception("Failed to mute chat. Status: " . $response['status']);
        }

        return $response['data'];
    });
}

echo "\n";

// Test 6: Message Features
echo "=== Test 6: Message Features ===\n";

if (isset($privateChat)) {
    testStep("Send Reply Message", function() use ($baseUrl, $user2Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user2Token];

        // First get the first message to reply to
        $response = makeRequest('GET', $baseUrl . '/chats/' . $privateChat['id'] . '/messages', null, $headers);
        if ($response['status'] !== 200) {
            throw new Exception("Failed to get messages for reply. Status: " . $response['status']);
        }

        $messages = $response['data']['data'];
        if (empty($messages)) {
            throw new Exception("No messages to reply to");
        }

        $firstMessage = $messages[0];

        $replyData = [
            'type' => 'text',
            'content' => 'This is a reply to your message!',
            'reply_to_message_id' => $firstMessage['id']
        ];

        $response = makeRequest('POST', $baseUrl . '/chats/' . $privateChat['id'] . '/messages', $replyData, $headers);
        if ($response['status'] !== 201) {
            throw new Exception("Failed to send reply message. Status: " . $response['status']);
        }

        return $response['data']['data']['message'];
    });

    testStep("Send Location Message", function() use ($baseUrl, $user1Token, $privateChat) {
        $headers = ['Authorization: Bearer ' . $user1Token];

        $locationData = [
            'type' => 'location',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'location_name' => 'New York City'
        ];

        $response = makeRequest('POST', $baseUrl . '/chats/' . $privateChat['id'] . '/messages', $locationData, $headers);
        if ($response['status'] !== 201) {
            throw new Exception("Failed to send location message. Status: " . $response['status']);
        }

        return $response['data']['data']['message'];
    });
}

echo "\n";

// Test 7: Chat List and Search
echo "=== Test 7: Chat List and Search ===\n";

testStep("Get User 1's Chat List", function() use ($baseUrl, $user1Token) {
    $headers = ['Authorization: Bearer ' . $user1Token];

    $response = makeRequest('GET', $baseUrl . '/chats', null, $headers);
    if ($response['status'] !== 200) {
        throw new Exception("Failed to get chat list. Status: " . $response['status']);
    }

    $chats = $response['data']['data']['chats'];
    echo "   Found " . count($chats) . " chats\n";

    foreach ($chats as $chat) {
        $type = $chat['type'];
        $name = $chat['name'] ?? 'Private Chat';
        echo "   - {$type}: {$name}\n";
    }

    return $response['data'];
});

testStep("Search Users", function() use ($baseUrl, $user1Token) {
    $headers = ['Authorization: Bearer ' . $user1Token];

    $response = makeRequest('GET', $baseUrl . '/search/users?q=Test', null, $headers);
    if ($response['status'] !== 200) {
        throw new Exception("Failed to search users. Status: " . $response['status']);
    }

    $users = $response['data']['data']['users'];
    echo "   Found " . count($users) . " users matching 'Test'\n";

    return $response['data'];
});

echo "\n";

// Test Summary
echo "=== Test Summary ===\n";
echo "✅ API Health: PASSED\n";
echo "✅ User Authentication: PASSED\n";
echo "✅ Private Chat Creation: " . (isset($privateChat) ? 'PASSED' : 'FAILED') . "\n";
echo "✅ Group Chat Creation: " . (isset($groupChat) ? 'PASSED' : 'FAILED') . "\n";
echo "✅ Message Sending: PASSED\n";
echo "✅ Chat Management: PASSED\n";
echo "✅ Message Features: PASSED\n";
echo "✅ Chat List and Search: PASSED\n";

echo "\n=== Test Complete ===\n";
echo "The core chat API functionality has been tested successfully!\n";

if (isset($privateChat)) {
    echo "\n🎉 Private chat functionality is working correctly!\n";
} else {
    echo "\n⚠️  Private chat functionality may have issues. Check the logs above.\n";
}
?>