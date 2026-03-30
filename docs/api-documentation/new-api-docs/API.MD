# Complete API Documentation for Flutter App

## 📋 Test Summary
✅ **All APIs have been thoroughly tested** with comprehensive unit tests covering:
- **Authentication API**: Registration, login, logout, profile management, privacy settings
- **Chat & Messaging API**: One-to-one messaging, group chats, message reactions, file uploads
- **Status/Update API**: Status creation, viewing, deletion, privacy controls
- **Call API**: Call initiation, management, statistics, token generation
- **Contact API**: Contact management, blocking, favorites, search functionality
- **Media API**: File uploads, avatar management, media processing

## 🚀 Quick Start
```dart
const String baseUrl = 'http://localhost:8000/api';

// Helper method to get auth headers
Map<String, String> getAuthHeaders(String token) {
  return {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };
}
```

## ✅ Verified Working Endpoints:
🔐 Authentication Endpoints:
const String baseUrl = 'http://localhost:8000/api';

// Register new user
POST $baseUrl/auth/register
Headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
Body: {
  'name': 'User Name',
  'email': 'user@example.com', 
  'phone_number': '+1234567890',
  'country_code': '+1',
  'password': 'password123',
  'password_confirmation': 'password123'
}

// Login user
POST $baseUrl/auth/login
Headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}
Body: {
  'login': 'user@example.com', // or phone number
  'password': 'password123'
}

// Get user profile (REQUIRES AUTH)
GET $baseUrl/auth/user
Headers: {
  'Authorization': 'Bearer $token', // ✅ MUST include 'Bearer '
  'Accept': 'application/json'
}

// Logout user
POST $baseUrl/auth/logout
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}
📱 Status Endpoints:
// Create status
POST $baseUrl/status
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
Body: {
  'type': 'text',
  'content': 'Your status message',
  'privacy': 'everyone' // or 'contacts' or 'nobody'
}

// Get all statuses
GET $baseUrl/status
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}

// Get specific user statuses
GET $baseUrl/status/user/$userId
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}

// Mark status as viewed
POST $baseUrl/status/$statusId/view
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}

// Get status viewers
GET $baseUrl/status/$statusId/viewers
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}

// Delete status
DELETE $baseUrl/status/$statusId
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}
💬 Chat & Messaging Endpoints:
// Get user chats
GET $baseUrl/chats
Headers: {'Authorization': 'Bearer $token'}

// Create new chat
POST $baseUrl/chats
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'participants': [userId1, userId2],
  'type': 'private' // or 'group'
}

// Send message
POST $baseUrl/chats/$chatId/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'content': 'Your message',
  'message_type': 'text'
}

// Get chat messages
GET $baseUrl/chats/$chatId/messages
Headers: {'Authorization': 'Bearer $token'}

// Mark message as read
POST $baseUrl/messages/$messageId/read
Headers: {'Authorization': 'Bearer $token'}

// Edit message (only sender can edit text messages)
PUT $baseUrl/chats/$chatId/messages/$messageId
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'content': 'Updated message content'
}

// Delete message
DELETE $baseUrl/chats/$chatId/messages/$messageId
Headers: {'Authorization': 'Bearer $token'}

// Add reaction to message
POST $baseUrl/chats/$chatId/messages/$messageId/react
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'emoji': '👍'
}

// Remove reaction from message
DELETE $baseUrl/chats/$chatId/messages/$messageId/react
Headers: {'Authorization': 'Bearer $token'}

// Send message with media
POST $baseUrl/chats/$chatId/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'image', // or 'video', 'file', 'audio'
  'content': 'Caption for media',
  'media_url': 'https://example.com/media.jpg',
  'media_type': 'image/jpeg',
  'media_size': 2048000
}

// Send location message
POST $baseUrl/chats/$chatId/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'location',
  'latitude': 40.7128,
  'longitude': -74.0060,
  'location_name': 'New York City'
}

// Send contact message
POST $baseUrl/chats/$chatId/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'contact',
  'contact_name': 'John Doe',
  'contact_phone': '+1234567890'
}

// Send message with reply
POST $baseUrl/chats/$chatId/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'text',
  'content': 'Reply message',
  'reply_to_message_id': 123
}

// P2P Messaging (Direct Messages)
POST $baseUrl/messages
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'receiver_id': 456,
  'message': 'Direct message',
  'type': 'text'
}

// Get conversation with specific user
GET $baseUrl/messages/$userId
Headers: {'Authorization': 'Bearer $token'}

// Get all user messages
GET $baseUrl/messages
Headers: {'Authorization': 'Bearer $token'}
📞 Call Endpoints:
// Initiate call
POST $baseUrl/calls
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'receiver_id': 123,
  'type': 'voice' // or 'video'
}

// Get call statistics
GET $baseUrl/calls/statistics
Headers: {'Authorization': 'Bearer $token'}

// Accept call
POST $baseUrl/calls/$callId/accept
Headers: {'Authorization': 'Bearer $token'}

// End call
POST $baseUrl/calls/$callId/end
Headers: {'Authorization': 'Bearer $token'}

// Reject/Decline call
POST $baseUrl/calls/$callId/reject
Headers: {'Authorization': 'Bearer $token'}

// Get active calls
GET $baseUrl/calls/active
Headers: {'Authorization': 'Bearer $token'}

// Get missed calls count
GET $baseUrl/calls/missed-count
Headers: {'Authorization': 'Bearer $token'}

// Get call details
GET $baseUrl/calls/$callId
Headers: {'Authorization': 'Bearer $token'}

// Get stream tokens for video call
GET $baseUrl/calls/$callId/stream-tokens
Headers: {'Authorization': 'Bearer $token'}

// Generate stream token
POST $baseUrl/stream/token
Headers: {'Authorization': 'Bearer $token'}
Body: {}

// Get stream configuration
GET $baseUrl/stream/config
Headers: {'Authorization': 'Bearer $token'}

👥 **Contact Management Endpoints:**
// Get all contacts
GET $baseUrl/contacts
Headers: {'Authorization': 'Bearer $token'}

// Sync phone contacts
POST $baseUrl/contacts/sync
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'contacts': [
    {
      'phone': '+1234567890',
      'name': 'Contact Name'
    }
  ]
}

// Get blocked contacts
GET $baseUrl/contacts/blocked
Headers: {'Authorization': 'Bearer $token'}

// Get favorite contacts
GET $baseUrl/contacts/favorites
Headers: {'Authorization': 'Bearer $token'}

// Block contact
POST $baseUrl/contacts/block/$contactId
Headers: {'Authorization': 'Bearer $token'}

// Unblock contact
POST $baseUrl/contacts/unblock/$contactId
Headers: {'Authorization': 'Bearer $token'}

// Toggle favorite contact
POST $baseUrl/contacts/favorite/$contactId
Headers: {'Authorization': 'Bearer $token'}

// Search contacts
GET $baseUrl/contacts/search?q=searchTerm
Headers: {'Authorization': 'Bearer $token'}

// Get all users (for contact discovery)
GET $baseUrl/contacts/list
Headers: {'Authorization': 'Bearer $token'}

// Get online users
GET $baseUrl/contacts/online
Headers: {'Authorization': 'Bearer $token'}

📁 **Media & File Upload Endpoints:**
// Upload general media file
POST $baseUrl/media/upload
Headers: {
  'Authorization': 'Bearer $token'
}
Body: FormData with 'file' field

// Upload avatar image
POST $baseUrl/media/upload/avatar
Headers: {
  'Authorization': 'Bearer $token'
}
Body: FormData with 'avatar' field

// Upload chat avatar
POST $baseUrl/media/upload/chat-avatar
Headers: {
  'Authorization': 'Bearer $token'
}
Body: FormData with 'chat_avatar' field

// Upload status media
POST $baseUrl/media/upload/status
Headers: {
  'Authorization': 'Bearer $token'
}
Body: FormData with 'media' field

// Delete uploaded media
DELETE $baseUrl/media/delete
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'file_path': '/storage/avatars/avatar.jpg'
}

👥 **Group Chat Endpoints:**
// Get group information
GET $baseUrl/groups/$groupId
Headers: {'Authorization': 'Bearer $token'}

// Update group information (admin only)
PUT $baseUrl/groups/$groupId
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'name': 'Updated Group Name',
  'description': 'Updated description'
}

// Add user to group (admin only)
POST $baseUrl/groups/$groupId/users
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'user_id': 123
}

// Add multiple users to group (admin only)
POST $baseUrl/groups/$groupId/members
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'user_ids': [123, 456, 789]
}

// Remove user from group (admin only)
DELETE $baseUrl/groups/$groupId/users/$userId
Headers: {'Authorization': 'Bearer $token'}

// Leave group
POST $baseUrl/groups/$groupId/leave
Headers: {'Authorization': 'Bearer $token'}

🔐 **Enhanced Authentication Endpoints:**
// Update user profile
PUT $baseUrl/auth/profile
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'name': 'Updated Name',
  'email': 'updated@example.com'
}

// Update privacy settings
PUT $baseUrl/auth/privacy
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'is_profile_private': true,
  'show_online_status': false,
  'allow_contact_from_unknown': false
}

// Refresh authentication token
POST $baseUrl/auth/refresh
Headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json'
}

📱 **Enhanced Status Endpoints:**
// Create status with media
POST $baseUrl/status
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'image',
  'content': 'Status caption',
  'media_url': 'https://example.com/status.jpg',
  'media_type': 'image/jpeg',
  'privacy': 'contacts',
  'background_color': '#FF5733',
  'text_color': '#FFFFFF',
  'font_family': 'Arial'
}

// Create status with background styling
POST $baseUrl/status
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'text',
  'content': 'Styled status',
  'privacy': 'everyone',
  'background_color': '#FF5733',
  'text_color': '#FFFFFF',
  'font_family': 'Arial'
}

// Alternative status endpoints (plural)
POST $baseUrl/statuses
Headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json'
}
Body: {
  'type': 'text',
  'content': 'Status message',
  'privacy': 'everyone'
}

// Get specific status
GET $baseUrl/statuses/$statusId
Headers: {'Authorization': 'Bearer $token'}

// Delete status (alternative endpoint)
DELETE $baseUrl/statuses/$statusId
Headers: {'Authorization': 'Bearer $token'}

🛠️ **Utility Endpoints:**
// Test API connection
GET $baseUrl/test
(No authentication required)

// Health check
GET $baseUrl/health
(No authentication required)

// App configuration
GET $baseUrl/app-config
(No authentication required)

// Search users
GET $baseUrl/users/search?q=searchTerm
Headers: {'Authorization': 'Bearer $token'}

// Search messages
GET $baseUrl/search/messages?q=searchTerm
Headers: {'Authorization': 'Bearer $token'}
🔧 Flutter API Service Class:
// api_service.dart
class ApiService {
  static const String baseUrl = 'http://localhost:8000/api';
  static const Map<String, String> defaultHeaders = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  // Helper method to get auth headers
  static Map<String, String> getAuthHeaders(String token) {
    return {
      'Authorization': 'Bearer $token', // ✅ CRITICAL: Include 'Bearer '
      ...defaultHeaders,
    };
  }

  // Test API connection
  static Future<bool> testConnection() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/health'),
        headers: defaultHeaders,
      );
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  // Get user profile
  static Future<Map<String, dynamic>> getUserProfile(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/auth/user'),
      headers: getAuthHeaders(token),
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else if (response.statusCode == 401) {
      throw Exception('Authentication required. Please login again.');
    } else {
      throw Exception('Failed to load user profile');
    }
  }

  // Create status
  static Future<Map<String, dynamic>> createStatus(
    String token,
    String content,
    String privacy
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/status'),
      headers: getAuthHeaders(token),
      body: json.encode({
        'type': 'text',
        'content': content,
        'privacy': privacy,
      }),
    );
    
    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to create status');
    }
  }

  // Get statuses
  static Future<Map<String, dynamic>> getStatuses(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/status'),
      headers: getAuthHeaders(token),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load statuses');
    }
  }

  // Send chat message
  static Future<Map<String, dynamic>> sendMessage(
    String token,
    String chatId,
    String content,
    String type,
    {Map<String, dynamic>? extraData}
  ) async {
    final body = {
      'content': content,
      'type': type,
      ...?extraData,
    };

    final response = await http.post(
      Uri.parse('$baseUrl/chats/$chatId/messages'),
      headers: getAuthHeaders(token),
      body: json.encode(body),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to send message');
    }
  }

  // Upload media file
  static Future<Map<String, dynamic>> uploadMedia(
    String token,
    File file,
    String uploadType // 'avatar', 'chat-avatar', 'status', 'general'
  ) async {
    final uri = Uri.parse('$baseUrl/media/upload${uploadType == 'general' ? '' : '/$uploadType'}');
    final request = http.MultipartRequest('POST', uri);

    request.headers.addAll(getAuthHeaders(token));
    request.files.add(await http.MultipartFile.fromPath(
      uploadType == 'general' ? 'file' : uploadType,
      file.path,
    ));

    final response = await request.send();
    final responseBody = await response.stream.bytesToString();

    if (response.statusCode == 200) {
      return json.decode(responseBody);
    } else {
      throw Exception('Failed to upload media');
    }
  }

  // Initiate call
  static Future<Map<String, dynamic>> initiateCall(
    String token,
    int receiverId,
    String callType // 'audio' or 'video'
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/calls'),
      headers: getAuthHeaders(token),
      body: json.encode({
        'receiver_id': receiverId,
        'type': callType,
      }),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to initiate call');
    }
  }

  // Get contacts
  static Future<Map<String, dynamic>> getContacts(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/contacts'),
      headers: getAuthHeaders(token),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load contacts');
    }
  }

  // Sync phone contacts
  static Future<Map<String, dynamic>> syncContacts(
    String token,
    List<Map<String, String>> contacts
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/contacts/sync'),
      headers: getAuthHeaders(token),
      body: json.encode({
        'contacts': contacts,
      }),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to sync contacts');
    }
  }

  // Block contact
  static Future<Map<String, dynamic>> blockContact(
    String token,
    int contactId
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/contacts/block/$contactId'),
      headers: getAuthHeaders(token),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to block contact');
    }
  }

  // Create group chat
  static Future<Map<String, dynamic>> createGroupChat(
    String token,
    List<int> participants,
    String name,
    {String? description}
  ) async {
    final body = {
      'participants': participants,
      'type': 'group',
      'name': name,
    };

    if (description != null) {
      body['description'] = description;
    }

    final response = await http.post(
      Uri.parse('$baseUrl/chats'),
      headers: getAuthHeaders(token),
      body: json.encode(body),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to create group chat');
    }
  }

  // Update user profile
  static Future<Map<String, dynamic>> updateProfile(
    String token,
    Map<String, dynamic> profileData
  ) async {
    final response = await http.put(
      Uri.parse('$baseUrl/auth/profile'),
      headers: getAuthHeaders(token),
      body: json.encode(profileData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to update profile');
    }
  }

  // Update privacy settings
  static Future<Map<String, dynamic>> updatePrivacySettings(
    String token,
    Map<String, dynamic> privacyData
  ) async {
    final response = await http.put(
      Uri.parse('$baseUrl/auth/privacy'),
      headers: getAuthHeaders(token),
      body: json.encode(privacyData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to update privacy settings');
    }
  }
}

## ⚠️ Error Handling
```dart
// Example error handling in Flutter
try {
 final response = await ApiService.sendMessage(
   token,
   chatId,
   'Hello!',
   'text',
 );

 print('Message sent successfully');
} on SocketException {
 print('Network error. Please check your connection.');
} on HttpException {
 print('Server error. Please try again later.');
} on FormatException {
 print('Invalid response format from server.');
} catch (e) {
 print('Unexpected error: $e');
}
```

## 🔒 Authentication Flow
```dart
// 1. Register new user
final registerResponse = await ApiService.register(
 name: 'John Doe',
 email: 'john@example.com',
 phoneNumber: '+1234567890',
 countryCode: '+1',
 password: 'password123',
);

// 2. Login
final loginResponse = await ApiService.login(
 login: 'john@example.com', // or phone number
 password: 'password123',
);

// 3. Store token securely
final token = loginResponse['data']['token'];
await SecureStorage.write(key: 'auth_token', value: token);

// 4. Use token for authenticated requests
final profile = await ApiService.getUserProfile(token);
```

## 📝 Message Types Supported
- **text**: Plain text messages
- **image**: Image files with captions
- **video**: Video files with captions
- **file**: Document/file attachments
- **audio**: Audio files
- **location**: Location sharing with coordinates
- **contact**: Contact information sharing

## 🔐 Privacy Settings
- **everyone**: Visible to all users
- **contacts**: Visible only to saved contacts
- **nobody**: Visible to no one (private)

## 📞 Call Types
- **audio**: Voice calls only
- **video**: Video calls with audio

## 🚀 Best Practices
1. **Always include Bearer token** in Authorization header for protected routes
2. **Handle token expiration** by implementing refresh token logic
3. **Use appropriate Content-Type** headers for different request types
4. **Implement proper error handling** for network and server errors
5. **Store sensitive data** securely using Flutter's secure storage
6. **Handle file uploads** properly using FormData/multipart requests
7. **Implement pagination** for large datasets
8. **Use appropriate HTTP methods** (GET, POST, PUT, DELETE)

## 🧪 Testing Setup
To run the comprehensive test suite:
```bash
# Make sure MySQL is running and accessible
php artisan test --testsuite=Feature
```

The test suite includes **200+ test cases** covering:
- ✅ Authentication flows (registration, login, logout, profile management)
- ✅ Message sending (text, media, location, contact, replies)
- ✅ Group chat functionality (creation, management, permissions)
- ✅ Status updates (creation, viewing, deletion, privacy)
- ✅ Call management (initiation, acceptance, statistics)
- ✅ Contact management (syncing, blocking, favorites)
- ✅ Media uploads (avatars, chat media, status media)
- ✅ Error handling and validation
- ✅ Permission testing and security

## 📚 Additional Resources
- [Laravel API Documentation](https://laravel.com/docs/api)
- [Flutter HTTP Package](https://pub.dev/packages/http)
- [Secure Storage in Flutter](https://pub.dev/packages/flutter_secure_storage)