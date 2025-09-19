# Chat API Testing Guide

This guide explains how to test the chat API functionality for creating, retrieving, and managing private and group chats.

## Overview

The chat API provides comprehensive functionality for:
- **Private Chats**: One-on-one conversations between two users
- **Group Chats**: Multi-user conversations with admin controls
- **Message Management**: Text, media, location, and contact messages
- **Chat Features**: Pin, mute, archive, and search functionality
- **User Management**: Adding/removing users from groups

## Test Scripts

### 1. `test-chat-api-comprehensive.php` (Recommended for Full Testing)

This script performs a complete end-to-end test of all chat functionality:

**Features Tested:**
- ✅ API health and connectivity
- ✅ User registration and authentication
- ✅ Private chat creation and management
- ✅ Group chat creation and management
- ✅ Message sending (text, location, contact)
- ✅ Reply messages
- ✅ Chat management (pin, mute, archive)
- ✅ User search and chat listing
- ✅ Group member management
- ✅ Message reactions and read status

**Usage:**
```bash
php test-chat-api-comprehensive.php
```

**What it does:**
1. Creates 3 test users automatically
2. Tests private chat between User 1 and User 2
3. Tests group chat with all 3 users
4. Sends various types of messages
5. Tests all chat management features
6. Provides detailed success/failure reporting

### 2. `test-chat-api-simple.php` (For Existing Users)

This script tests core functionality using existing user accounts:

**Features Tested:**
- ✅ API health check
- ✅ User authentication verification
- ✅ Private chat creation
- ✅ Basic message functionality
- ✅ Chat management features
- ✅ Chat listing and search

**Usage:**
1. Edit the script and add your user tokens:
```php
$user1Token = 'your_user1_token_here';
$user2Token = 'your_user2_token_here';
$user3Token = 'your_user3_token_here'; // Optional
```

2. Run the script:
```bash
php test-chat-api-simple.php
```

## Prerequisites

### 1. Laravel Server Running
Ensure your Laravel server is running:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Database Setup
Make sure your database is configured and migrations are run:
```bash
php artisan migrate
```

### 3. API Configuration
Verify your `.env` file has the correct API settings:
```env
APP_URL=http://192.168.0.6:8000
API_HOST=192.168.0.6
API_PORT=8000
```

## API Endpoints Tested

### Chat Management
- `POST /api/chats` - Create private/group chat
- `GET /api/chats` - Get user's chat list
- `GET /api/chats/{id}` - Get specific chat details
- `PUT /api/chats/{id}` - Update chat (group only)
- `POST /api/chats/{id}/archive` - Archive/unarchive chat
- `POST /api/chats/{id}/pin` - Pin/unpin chat
- `POST /api/chats/{id}/mute` - Mute chat
- `POST /api/chats/{id}/leave` - Leave group chat

### Messaging
- `POST /api/chats/{id}/messages` - Send message
- `GET /api/chats/{id}/messages` - Get chat messages
- `POST /api/chats/{id}/messages/{id}/read` - Mark as read
- `POST /api/chats/{id}/messages/{id}/react` - Add reaction
- `DELETE /api/chats/{id}/messages/{id}/react` - Remove reaction

### Group Management
- `GET /api/groups` - Get user's groups
- `POST /api/groups` - Create group
- `GET /api/groups/{id}` - Get group details
- `POST /api/groups/{id}/users` - Add user to group
- `DELETE /api/groups/{id}/users/{userId}` - Remove user from group

### User Management
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `GET /api/auth/user` - Get user info
- `GET /api/search/users` - Search users

## Message Types Supported

1. **Text Messages**
   ```json
   {
     "type": "text",
     "content": "Hello world!"
   }
   ```

2. **Location Messages**
   ```json
   {
     "type": "location",
     "latitude": 40.7128,
     "longitude": -74.0060,
     "location_name": "New York City"
   }
   ```

3. **Contact Messages**
   ```json
   {
     "type": "contact",
     "contact_name": "John Doe",
     "contact_phone": "+12345678900"
   }
   ```

4. **Reply Messages**
   ```json
   {
     "type": "text",
     "content": "This is a reply",
     "reply_to_message_id": 123
   }
   ```

## Expected Test Results

### Successful Test Run
```
=== Test Summary ===
✅ API Health: PASSED
✅ User Authentication: PASSED
✅ Private Chat Creation: PASSED
✅ Group Chat Creation: PASSED
✅ Message Sending: PASSED
✅ Chat Management: PASSED
✅ Message Features: PASSED
✅ Group Management: PASSED
✅ Message Interactions: PASSED

🎉 All core chat functionality is working correctly!
```

### Common Issues and Solutions

1. **API Connection Failed**
   - Check if Laravel server is running
   - Verify IP address and port in script
   - Check firewall settings

2. **Authentication Failed**
   - Verify user credentials
   - Check if Sanctum is properly configured
   - Ensure database has user records

3. **Chat Creation Failed**
   - Check database migrations
   - Verify user IDs exist
   - Check validation rules

4. **Message Sending Failed**
   - Verify chat exists and user is participant
   - Check message validation rules
   - Ensure proper message type

## Database Schema

The chat system uses these main tables:

- **users** - User accounts
- **chats** - Chat rooms (private/group)
- **chat_participants** - Chat membership with roles
- **messages** - Chat messages
- **message_reactions** - Message reactions

## Real-time Features

The API supports real-time messaging through:
- WebSocket connections
- Pusher/Reverb integration
- Message broadcasting events

## Security Features

- JWT token authentication
- User authorization checks
- Input validation and sanitization
- SQL injection protection
- XSS protection

## Performance Considerations

- Pagination for message retrieval
- Eager loading of relationships
- Database indexing on frequently queried fields
- Message caching for better performance

## Troubleshooting

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Verify Database Connection
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Test API Manually
```bash
curl -X GET "http://192.168.0.6:8000/api/test"
```

### Check Environment Variables
```bash
php artisan config:cache
php artisan config:clear
```

## Support

If you encounter issues:
1. Check the test output for specific error messages
2. Verify all prerequisites are met
3. Check Laravel logs for detailed error information
4. Ensure database schema is correct
5. Verify API routes are properly registered

## Next Steps

After successful testing:
1. Integrate with your frontend application
2. Implement real-time features using WebSockets
3. Add additional message types as needed
4. Implement push notifications
5. Add message encryption for security
