# WhatsApp Clone - Backend PRD & API Specifications

## Table of Contents
1. [Product Overview](#product-overview)
2. [Technical Stack](#technical-stack)
3. [Database Schema](#database-schema)
4. [Authentication System](#authentication-system)
5. [API Endpoints](#api-endpoints)
6. [Real-time Features](#real-time-features)
7. [File Storage & Media](#file-storage--media)
8. [Security Requirements](#security-requirements)
9. [Performance Requirements](#performance-requirements)
10. [Deployment Architecture](#deployment-architecture)

## Product Overview

### Core Features
- **Authentication**: Phone number verification, JWT tokens
- **Messaging**: Text, voice, image, video, document sharing
- **Group Chats**: Create, manage, and participate in group conversations
- **Status/Stories**: Share temporary status updates with media
- **Voice/Video Calls**: Real-time audio and video communication
- **User Management**: Profiles, contacts, privacy settings
- **Real-time Updates**: Live message delivery, typing indicators, online status

### User Types
- **Regular User**: Can send messages, join groups, make calls
- **Group Admin**: Can manage group settings, add/remove members
- **System Admin**: Can moderate content, manage users

## Technical Stack

### Backend Framework
- **Framework**: Laravel 10.x
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0+ / PostgreSQL 14+
- **Cache**: Redis 7.0+
- **Queue**: Redis/Database
- **WebSocket**: Laravel Reverb / Pusher
- **File Storage**: AWS S3 / MinIO / Local Storage

### Key Laravel Packages
```bash
# Core Dependencies
laravel/sanctum          # API Authentication
pusher/pusher-php-server # Real-time messaging
intervention/image       # Image processing
spatie/laravel-permission # Role/Permission management
laravel/horizon          # Queue monitoring
predis/predis           # Redis client

# Additional Packages
league/flysystem-aws-s3-v3  # S3 Storage
twilio/sdk                  # SMS verification
firebase/php-jwt            # JWT handling
spatie/laravel-medialibrary # Media management
```

## Database Schema

### Core Tables

#### 1. Users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    country_code VARCHAR(5) NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    about TEXT DEFAULT 'Hey there! I am using WhatsApp Clone.',
    last_seen_at TIMESTAMP NULL,
    is_online BOOLEAN DEFAULT FALSE,
    privacy_last_seen ENUM('everyone', 'contacts', 'nobody') DEFAULT 'everyone',
    privacy_profile_photo ENUM('everyone', 'contacts', 'nobody') DEFAULT 'everyone',
    privacy_about ENUM('everyone', 'contacts', 'nobody') DEFAULT 'everyone',
    phone_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_phone_number (phone_number),
    INDEX idx_last_seen (last_seen_at),
    INDEX idx_online_status (is_online)
);
```

#### 2. Chats Table
```sql
CREATE TABLE chats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    type ENUM('private', 'group') NOT NULL,
    name VARCHAR(255) NULL, -- For group chats
    description TEXT NULL,
    avatar_url VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_created_by (created_by)
);
```

#### 3. Chat Participants Table
```sql
CREATE TABLE chat_participants (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    muted_until TIMESTAMP NULL,
    last_read_message_id BIGINT UNSIGNED NULL,
    
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_chat_user (chat_id, user_id),
    INDEX idx_chat_id (chat_id),
    INDEX idx_user_id (user_id)
);
```

#### 4. Messages Table
```sql
CREATE TABLE messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    reply_to_message_id BIGINT UNSIGNED NULL,
    message_type ENUM('text', 'image', 'video', 'audio', 'document', 'location', 'contact') NOT NULL,
    content TEXT NULL,
    media_url VARCHAR(500) NULL,
    media_size BIGINT NULL,
    media_duration INT NULL, -- For audio/video in seconds
    media_mime_type VARCHAR(100) NULL,
    file_name VARCHAR(255) NULL,
    thumbnail_url VARCHAR(500) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    location_name VARCHAR(255) NULL,
    contact_data JSON NULL,
    status ENUM('sending', 'sent', 'delivered', 'read') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    edited_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_message_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_chat_id (chat_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_status (status)
);
```

#### 5. Message Reactions Table
```sql
CREATE TABLE message_reactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_message_reaction (message_id, user_id, emoji),
    INDEX idx_message_id (message_id)
);
```

#### 6. Status/Stories Tables
```sql
CREATE TABLE statuses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    content_type ENUM('text', 'image', 'video') NOT NULL,
    content TEXT NULL,
    media_url VARCHAR(500) NULL,
    background_color VARCHAR(7) NULL,
    font_style VARCHAR(50) NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE status_views (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    status_id BIGINT UNSIGNED NOT NULL,
    viewer_id BIGINT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (status_id) REFERENCES statuses(id) ON DELETE CASCADE,
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_status_viewer (status_id, viewer_id)
);
```

#### 7. Contacts Table
```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    contact_user_id BIGINT UNSIGNED NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    is_blocked BOOLEAN DEFAULT FALSE,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_contact (user_id, contact_user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_contact_user_id (contact_user_id)
);
```

#### 8. Calls Table
```sql
CREATE TABLE calls (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT UNSIGNED NOT NULL,
    caller_id BIGINT UNSIGNED NOT NULL,
    call_type ENUM('audio', 'video') NOT NULL,
    status ENUM('initiated', 'ringing', 'answered', 'ended', 'missed', 'declined') NOT NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    duration INT NULL, -- in seconds
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (caller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_chat_id (chat_id),
    INDEX idx_caller_id (caller_id),
    INDEX idx_created_at (created_at)
);
```

## Authentication System

### Phone Number Verification Flow
1. User enters phone number
2. System sends OTP via SMS (Twilio)
3. User verifies OTP
4. System creates/updates user account
5. Returns JWT access token + refresh token

### JWT Token Structure
```json
{
  "user_id": 123,
  "phone_number": "+1234567890",
  "exp": 1640995200,
  "iat": 1640908800,
  "type": "access"
}
```

## API Endpoints

### Authentication APIs

#### 1. Send OTP
```http
POST /api/auth/send-otp
Content-Type: application/json

{
  "phone_number": "+1234567890",
  "country_code": "+1"
}

Response 200:
{
  "success": true,
  "message": "OTP sent successfully",
  "expires_at": "2024-01-01T12:05:00Z"
}
```

#### 2. Verify OTP
```http
POST /api/auth/verify-otp
Content-Type: application/json

{
  "phone_number": "+1234567890",
  "otp": "123456"
}

Response 200:
{
  "success": true,
  "user": {
    "id": 123,
    "phone_number": "+1234567890",
    "name": "John Doe",
    "avatar_url": "https://...",
    "about": "Hey there!",
    "created_at": "2024-01-01T12:00:00Z"
  },
  "tokens": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2024-01-01T13:00:00Z"
  }
}
```

#### 3. Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {refresh_token}

Response 200:
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_at": "2024-01-01T14:00:00Z"
}
```

#### 4. Logout
```http
POST /api/auth/logout
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "message": "Logged out successfully"
}
```

### User Management APIs

#### 1. Get User Profile
```http
GET /api/user/profile
Authorization: Bearer {access_token}

Response 200:
{
  "id": 123,
  "phone_number": "+1234567890",
  "name": "John Doe",
  "avatar_url": "https://...",
  "about": "Hey there!",
  "privacy_settings": {
    "last_seen": "everyone",
    "profile_photo": "contacts",
    "about": "everyone"
  },
  "last_seen_at": "2024-01-01T12:00:00Z",
  "is_online": true
}
```

#### 2. Update User Profile
```http
PUT /api/user/profile
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "John Smith",
  "about": "Living life to the fullest!"
}

Response 200:
{
  "success": true,
  "user": {
    "id": 123,
    "name": "John Smith",
    "about": "Living life to the fullest!",
    "updated_at": "2024-01-01T12:30:00Z"
  }
}
```

#### 3. Upload Avatar
```http
POST /api/user/avatar
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

avatar: [image file]

Response 200:
{
  "success": true,
  "avatar_url": "https://storage.example.com/avatars/123.jpg"
}
```

#### 4. Update Privacy Settings
```http
PUT /api/user/privacy
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "last_seen": "contacts",
  "profile_photo": "everyone",
  "about": "nobody"
}

Response 200:
{
  "success": true,
  "privacy_settings": {
    "last_seen": "contacts",
    "profile_photo": "everyone",
    "about": "nobody"
  }
}
```

### Chat Management APIs

#### 1. Get All Chats
```http
GET /api/chats?page=1&limit=20
Authorization: Bearer {access_token}

Response 200:
{
  "chats": [
    {
      "id": 1,
      "type": "private",
      "participant": {
        "id": 456,
        "name": "Jane Smith",
        "avatar_url": "https://...",
        "last_seen_at": "2024-01-01T11:30:00Z",
        "is_online": false
      },
      "last_message": {
        "id": 789,
        "content": "Hey, how are you?",
        "message_type": "text",
        "sent_at": "2024-01-01T11:30:00Z",
        "status": "read"
      },
      "unread_count": 2,
      "updated_at": "2024-01-01T11:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 95
  }
}
```

#### 2. Get Chat Messages
```http
GET /api/chats/1/messages?page=1&limit=50&before_message_id=100
Authorization: Bearer {access_token}

Response 200:
{
  "messages": [
    {
      "id": 99,
      "sender": {
        "id": 456,
        "name": "Jane Smith",
        "avatar_url": "https://..."
      },
      "content": "Hello there!",
      "message_type": "text",
      "reply_to": null,
      "status": "read",
      "reactions": [
        {
          "emoji": "👍",
          "count": 2,
          "users": [{"id": 123, "name": "John"}]
        }
      ],
      "sent_at": "2024-01-01T11:25:00Z",
      "delivered_at": "2024-01-01T11:25:01Z",
      "read_at": "2024-01-01T11:25:05Z"
    }
  ],
  "pagination": {
    "has_more": true,
    "next_before_message_id": 90
  }
}
```

#### 3. Send Message
```http
POST /api/chats/1/messages
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "message_type": "text",
  "content": "Hello from API!",
  "reply_to_message_id": 98
}

Response 201:
{
  "message": {
    "id": 100,
    "chat_id": 1,
    "sender_id": 123,
    "content": "Hello from API!",
    "message_type": "text",
    "reply_to_message_id": 98,
    "status": "sent",
    "sent_at": "2024-01-01T12:00:00Z"
  }
}
```

#### 4. Send Media Message
```http
POST /api/chats/1/messages
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

message_type: image
file: [image file]
caption: "Check this out!"

Response 201:
{
  "message": {
    "id": 101,
    "chat_id": 1,
    "sender_id": 123,
    "message_type": "image",
    "content": "Check this out!",
    "media_url": "https://storage.example.com/media/101.jpg",
    "thumbnail_url": "https://storage.example.com/thumbnails/101.jpg",
    "media_size": 1048576,
    "status": "sent",
    "sent_at": "2024-01-01T12:01:00Z"
  }
}
```

#### 5. Mark Messages as Read
```http
PUT /api/chats/1/read
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "message_id": 100
}

Response 200:
{
  "success": true,
  "read_at": "2024-01-01T12:05:00Z"
}
```

#### 6. Delete Message
```http
DELETE /api/messages/100
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "message": "Message deleted successfully"
}
```

#### 7. React to Message
```http
POST /api/messages/100/reactions
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "emoji": "👍"
}

Response 200:
{
  "success": true,
  "reaction": {
    "message_id": 100,
    "emoji": "👍",
    "count": 3
  }
}
```

### Group Chat APIs

#### 1. Create Group
```http
POST /api/groups
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

name: "Dev Team"
description: "Development team chat"
avatar: [image file]
participants: [456, 789, 101]

Response 201:
{
  "group": {
    "id": 5,
    "name": "Dev Team",
    "description": "Development team chat",
    "avatar_url": "https://storage.example.com/groups/5.jpg",
    "created_by": 123,
    "participants_count": 4,
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

#### 2. Get Group Info
```http
GET /api/groups/5
Authorization: Bearer {access_token}

Response 200:
{
  "id": 5,
  "name": "Dev Team",
  "description": "Development team chat",
  "avatar_url": "https://...",
  "created_by": {
    "id": 123,
    "name": "John Doe"
  },
  "participants": [
    {
      "id": 123,
      "name": "John Doe",
      "avatar_url": "https://...",
      "role": "admin",
      "joined_at": "2024-01-01T12:00:00Z"
    },
    {
      "id": 456,
      "name": "Jane Smith",
      "avatar_url": "https://...",
      "role": "member",
      "joined_at": "2024-01-01T12:00:00Z"
    }
  ],
  "created_at": "2024-01-01T12:00:00Z"
}
```

#### 3. Add Group Members
```http
POST /api/groups/5/members
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "user_ids": [111, 222]
}

Response 200:
{
  "success": true,
  "added_members": [
    {"id": 111, "name": "Alice"},
    {"id": 222, "name": "Bob"}
  ]
}
```

#### 4. Remove Group Member
```http
DELETE /api/groups/5/members/456
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "message": "Member removed successfully"
}
```

#### 5. Update Group Info
```http
PUT /api/groups/5
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Updated Dev Team",
  "description": "Updated description"
}

Response 200:
{
  "success": true,
  "group": {
    "id": 5,
    "name": "Updated Dev Team",
    "description": "Updated description",
    "updated_at": "2024-01-01T12:30:00Z"
  }
}
```

#### 6. Leave Group
```http
POST /api/groups/5/leave
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "message": "Left group successfully"
}
```

### Status/Stories APIs

#### 1. Create Status
```http
POST /api/status
Authorization: Bearer {access_token}
Content-Type: multipart/form-data

content_type: image
media: [image file]
content: "Having a great day!"

Response 201:
{
  "status": {
    "id": 25,
    "content_type": "image",
    "content": "Having a great day!",
    "media_url": "https://storage.example.com/status/25.jpg",
    "expires_at": "2024-01-02T12:00:00Z",
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

#### 2. Get Status Updates
```http
GET /api/status
Authorization: Bearer {access_token}

Response 200:
{
  "statuses": [
    {
      "user": {
        "id": 456,
        "name": "Jane Smith",
        "avatar_url": "https://..."
      },
      "statuses": [
        {
          "id": 24,
          "content_type": "text",
          "content": "Good morning!",
          "background_color": "#25D366",
          "created_at": "2024-01-01T08:00:00Z",
          "is_viewed": false
        }
      ],
      "latest_status_at": "2024-01-01T08:00:00Z"
    }
  ]
}
```

#### 3. View Status
```http
POST /api/status/24/view
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "viewed_at": "2024-01-01T12:00:00Z"
}
```

#### 4. Get Status Views
```http
GET /api/status/25/views
Authorization: Bearer {access_token}

Response 200:
{
  "views": [
    {
      "viewer": {
        "id": 456,
        "name": "Jane Smith",
        "avatar_url": "https://..."
      },
      "viewed_at": "2024-01-01T12:05:00Z"
    }
  ],
  "total_views": 1
}
```

### Contact Management APIs

#### 1. Get Contacts
```http
GET /api/contacts
Authorization: Bearer {access_token}

Response 200:
{
  "contacts": [
    {
      "id": 456,
      "name": "Jane Smith",
      "phone_number": "+1234567891",
      "avatar_url": "https://...",
      "about": "Living life!",
      "is_contact": true,
      "is_blocked": false,
      "last_seen_at": "2024-01-01T11:30:00Z",
      "is_online": false
    }
  ]
}
```

#### 2. Add Contact
```http
POST /api/contacts
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "phone_number": "+1234567892",
  "contact_name": "Mike Johnson"
}

Response 201:
{
  "success": true,
  "contact": {
    "id": 789,
    "name": "Mike Johnson",
    "phone_number": "+1234567892"
  }
}
```

#### 3. Block/Unblock Contact
```http
PUT /api/contacts/456/block
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "is_blocked": true
}
```

### Call Management APIs

#### 1. Initiate Call
```http
POST /api/calls
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "chat_id": 1,
  "call_type": "video"
}

Response 201:
{
  "call": {
    "id": 10,
    "chat_id": 1,
    "call_type": "video",
    "status": "initiated",
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

#### 2. Answer Call
```http
PUT /api/calls/10/answer
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "call": {
    "id": 10,
    "status": "answered",
    "started_at": "2024-01-01T12:00:05Z"
  }
}
```

#### 3. End Call
```http
PUT /api/calls/10/end
Authorization: Bearer {access_token}

Response 200:
{
  "success": true,
  "call": {
    "id": 10,
    "status": "ended",
    "ended_at": "2024-01-01T12:05:30Z",
    "duration": 325
  }
}
```

#### 4. Get Call History
```http
GET /api/calls?page=1&limit=20
Authorization: Bearer {access_token}

Response 200:
{
  "calls": [
    {
      "id": 10,
      "chat": {
        "id": 1,
        "participant": {
          "id": 456,
          "name": "Jane Smith",
          "avatar_url": "https://..."
        }
      },
      "call_type": "video",
      "status": "ended",
      "duration": 325,
      "created_at": "2024-01-01T12:00:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3
  }
}
```

## Real-time Features

### WebSocket Events (Laravel Reverb/Pusher)

#### Channel Structure
```
private-user.{user_id}           # Personal notifications
private-chat.{chat_id}           # Chat-specific events
presence-chat.{chat_id}          # Typing indicators, online status
```

#### Real-time Events

##### 1. New Message
```javascript
// Channel: private-chat.{chat_id}
// Event: new-message
{
  "message": {
    "id": 101,
    "sender": {
      "id": 456,
      "name": "Jane Smith",
      "avatar_url": "https://..."
    },
    "content": "Hello!",
    "message_type": "text",
    "sent_at": "2024-01-01T12:00:00Z"
  }
}
```

##### 2. Message Status Update
```javascript
// Channel: private-chat.{chat_id}
// Event: message-status-updated
{
  "message_id": 101,
  "status": "read",
  "read_at": "2024-01-01T12:01:00Z",
  "read_by": {
    "id": 123,
    "name": "John Doe"
  }
}
```

##### 3. Typing Indicator
```javascript
// Channel: presence-chat.{chat_id}
// Event: user-typing
{
  "user": {
    "id": 456,
    "name": "Jane Smith"
  },
  "is_typing": true
}
```

##### 4. User Online Status
```javascript
// Channel: private-user.{user_id}
// Event: user-status-updated
{
  "contact_id": 456,
  "is_online": true,
  "last_seen_at": "2024-01-01T12:00:00Z"
}
```

##### 5. New Call
```javascript
// Channel: private-chat.{chat_id}
// Event: call-initiated
{
  "call": {
    "id": 15,
    "caller": {
      "id": 456,
      "name": "Jane Smith",
      "avatar_url": "https://..."
    },
    "call_type": "video",
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

## File Storage & Media

### Storage Structure
```
storage/
├── avatars/
│   ├── users/{user_id}.{ext}
│   └── groups/{group_id}.{ext}
├── messages/
│   ├── images/{year}/{month}/{message_id}.{ext}
│   ├── videos/{year}/{month}/{message_id}.{ext}
│   ├── audio/{year}/{month}/{message_id}.{ext}
│   └── documents/{year}/{month}/{message_id}.{ext}
├── thumbnails/
│   ├── images/{message_id}_thumb.jpg
│   └── videos/{message_id}_thumb.jpg
└── status/
    └── {year}/{month}/{status_id}.{ext}
```

### File Upload Configuration
```php
// config/filesystems.php
'media' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
],

// File size limits
'max_file_sizes' => [
    'image' => 10 * 1024 * 1024,    // 10MB
    'video' => 100 * 1024 * 1024,   // 100MB
    'audio' => 50 * 1024 * 1024,    // 50MB
    'document' => 20 * 1024 * 1024, // 20MB
],
```

### Image Processing Pipeline
```php
// Auto-resize images
'image_processing' => [
    'avatar' => [
        'sizes' => [150, 300, 600],
        'quality' => 80,
        'format' => 'jpg'
    ],
    'message_image' => [
        'max_width' => 1200,
        'max_height' => 1200,
        'quality' => 85,
        'thumbnail' => [300, 300]
    ]
],
```

## Security Requirements

### Authentication & Authorization
- **JWT Token Expiry**: Access tokens expire in 1 hour
- **Refresh Token Rotation**: New refresh token on each use
- **Rate Limiting**: 
  - Auth endpoints: 5 requests/minute
  - Message sending: 60 messages/minute
  - File uploads: 10 uploads/minute

### Data Protection
- **Encryption at Rest**: Database encryption for sensitive data
- **Encryption in Transit**: TLS 1.3 for all API communications
- **File Encryption**: Server-side encryption for stored media
- **PII Handling**: Phone numbers hashed for indexing

### Privacy Controls
- **Message Deletion**: Hard delete after 30 days
- **Media Auto-deletion**: Status media deleted after 24 hours
- **Account Deletion**: Complete data purge within 30 days
- **Data Export**: User can request data export

### Spam & Abuse Protection
- **Message Filtering**: Content moderation for inappropriate content
- **Rate Limiting**: Per-user message limits
- **Report System**: Users can report inappropriate content
- **Automatic Blocking**: AI-based spam detection

## Performance Requirements

### Response Time Targets
- **Authentication**: < 500ms
- **Message Sending**: < 200ms
- **Message Loading**: < 300ms
- **File Upload**: < 2s for 10MB files
- **Real-time Delivery**: < 100ms

### Scalability Targets
- **Concurrent Users**: Support 100,000 concurrent connections
- **Messages/Second**: Handle 10,000 messages per second
- **Database**: Horizontal scaling with read replicas
- **File Storage**: CDN distribution for global access

### Caching Strategy
```php
// Redis caching
'cache_ttl' => [
    'user_profile' => 3600,      // 1 hour
    'chat_participants' => 1800, // 30 minutes
    'user_contacts' => 7200,     // 2 hours
    'message_threads' => 300,    // 5 minutes
],
```

### Database Optimization
- **Indexing**: Optimized indexes for frequent queries
- **Partitioning**: Messages table partitioned by date
- **Archiving**: Old messages moved to archive tables
- **Connection Pooling**: Efficient database connection management

## Deployment Architecture

### Infrastructure Requirements
```yaml
# Minimum Production Setup
web_servers:
  count: 3
  specs: 4 CPU, 8GB RAM, SSD
  load_balancer: nginx/cloudflare

database:
  primary: 8 CPU, 16GB RAM, SSD
  replicas: 2 read replicas
  
cache:
  redis_cluster: 3 nodes, 4GB RAM each
  
queue_workers:
  count: 5
  specs: 2 CPU, 4GB RAM

storage:
  type: AWS S3 / MinIO
  cdn: CloudFlare / AWS CloudFront
```

### Environment Configuration
```bash
# Production .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.whatsapp-clone.com

DB_CONNECTION=mysql
DB_HOST=db-primary.internal
DB_DATABASE=whatsapp_clone
DB_USERNAME=app_user
DB_PASSWORD=${DB_PASSWORD}

REDIS_HOST=redis-cluster.internal
REDIS_PASSWORD=${REDIS_PASSWORD}

BROADCAST_DRIVER=reverb
QUEUE_CONNECTION=redis

AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=whatsapp-clone-media

TWILIO_SID=${TWILIO_SID}
TWILIO_TOKEN=${TWILIO_TOKEN}
TWILIO_FROM=${TWILIO_FROM}
```

### Monitoring & Logging
- **Application Monitoring**: Laravel Telescope + Sentry
- **Performance Monitoring**: New Relic / DataDog
- **Log Aggregation**: ELK Stack (Elasticsearch, Logstash, Kibana)
- **Uptime Monitoring**: Pingdom / UptimeRobot
- **Error Tracking**: Sentry for real-time error reporting

### Backup Strategy
- **Database**: Daily full backup + hourly incremental
- **Media Files**: Daily sync to backup storage
- **Application**: Automated deployment rollback capability
- **Recovery Time**: RTO < 4 hours, RPO < 1 hour

This comprehensive PRD covers all aspects of the WhatsApp clone backend implementation with Laravel. The API is designed to be RESTful, scalable, and secure, with real-time capabilities for a modern messaging experience. 