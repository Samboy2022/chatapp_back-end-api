# Call Signaling API Documentation

## Overview

This documentation covers the Laravel backend API endpoints for video/voice call signaling that integrates with your existing broadcast settings system.

## Base URL

```
Development: http://127.0.0.1:8000
Production: https://your-production-domain.com
```

## Authentication

All API endpoints require Bearer token authentication:

```
Authorization: Bearer {your_access_token}
```

## Broadcast Settings Endpoints

### 1. Get Broadcast Configuration

**Endpoint:** `GET /api/broadcast-settings`

**Description:** Get current broadcast settings including call signaling configuration.

**Response:**
```json
{
  "success": true,
  "data": {
    "enabled": true,
    "driver": "pusher",
    "config": {
      "key": "your_pusher_key",
      "cluster": "mt1",
      "scheme": "https",
      "host": "ws-mt1.pusher.com",
      "port": 443,
      "auth_endpoint": "http://127.0.0.1:8000/broadcasting/auth"
    },
    "connection_status": {
      "connected": true,
      "message": "Connection successful"
    },
    "call_signaling": {
      "enabled": true,
      "channel_pattern": "call.{userId}",
      "events": [
        "CallInitiated",
        "CallAccepted",
        "CallEnded",
        "CallRejected"
      ],
      "auth_required": true
    },
    "last_updated": "2025-07-15T10:30:00.000000Z"
  }
}
```

### 2. Get Call Signaling Configuration

**Endpoint:** `GET /api/broadcast-settings/call-signaling`

**Description:** Get detailed call signaling configuration for mobile apps.

**Response:**
```json
{
  "success": true,
  "data": {
    "enabled": true,
    "driver": "pusher",
    "websocket_config": {
      "key": "your_pusher_key",
      "cluster": "mt1",
      "auth_endpoint": "http://127.0.0.1:8000/broadcasting/auth"
    },
    "call_channels": {
      "private_pattern": "call.{userId}",
      "description": "Subscribe to call.{your_user_id} to receive call events",
      "auth_endpoint": "http://127.0.0.1:8000/broadcasting/auth",
      "requires_authentication": true
    },
    "call_events": {
      "CallInitiated": {
        "description": "Fired when someone initiates a call to you",
        "data_structure": {
          "event_type": "call_initiated",
          "call_id": "unique_call_identifier",
          "caller_id": "caller_user_id",
          "recipient_id": "recipient_user_id",
          "caller_name": "caller_display_name",
          "caller_avatar": "caller_avatar_url",
          "call_type": "voice|video",
          "timestamp": "ISO_timestamp",
          "metadata": "additional_call_data"
        }
      },
      "CallAccepted": {
        "description": "Fired when a call is accepted by the recipient",
        "data_structure": {
          "event_type": "call_accepted",
          "call_id": "unique_call_identifier",
          "caller_id": "caller_user_id",
          "recipient_id": "recipient_user_id",
          "timestamp": "ISO_timestamp",
          "metadata": "call_session_data"
        }
      },
      "CallEnded": {
        "description": "Fired when a call is ended by either party",
        "data_structure": {
          "event_type": "call_ended",
          "call_id": "unique_call_identifier",
          "caller_id": "caller_user_id",
          "recipient_id": "recipient_user_id",
          "timestamp": "ISO_timestamp",
          "metadata": {
            "duration": "call_duration_seconds",
            "ended_by": "user_who_ended_call"
          }
        }
      },
      "CallRejected": {
        "description": "Fired when a call is rejected by the recipient",
        "data_structure": {
          "event_type": "call_rejected",
          "call_id": "unique_call_identifier",
          "caller_id": "caller_user_id",
          "recipient_id": "recipient_user_id",
          "timestamp": "ISO_timestamp"
        }
      }
    },
    "integration_guide": {
      "step_1": "Check if call signaling is enabled via this endpoint",
      "step_2": "Initialize WebSocket connection using websocket_config",
      "step_3": "Subscribe to private channel: call.{your_user_id}",
      "step_4": "Listen for CallInitiated, CallAccepted, CallEnded, CallRejected events",
      "step_5": "Handle events to trigger WebRTC session establishment"
    }
  }
}
```

## Stream Video Integration

### Stream Video Token Generation

**Endpoint:** `POST /api/stream/token`

**Description:** Generate Stream video token for authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "api_key": "your_stream_api_key",
    "user_id": 123,
    "expires_at": "2025-07-16T10:30:00.000000Z",
    "call_id": "optional_call_id",
    "room_id": "optional_room_id"
  },
  "message": "Stream video token generated successfully"
}
```

**Endpoint:** `GET /api/stream/config`

**Description:** Get Stream configuration for frontend.

**Response:**
```json
{
  "success": true,
  "data": {
    "api_key": "your_stream_api_key",
    "token_validity_hours": 24,
    "features": {
      "video_calling": true,
      "screen_sharing": true,
      "recording": false
    }
  },
  "message": "Stream configuration retrieved successfully"
}
```

**Endpoint:** `GET /api/calls/{call_id}/stream-tokens`

**Description:** Get Stream video tokens for a specific call.

**Response:**
```json
{
  "success": true,
  "data": {
    "call_id": 456,
    "caller_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "receiver_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "api_key": "your_stream_api_key",
    "expires_at": "2025-07-16T10:30:00.000000Z",
    "room_id": "call_456"
  },
  "message": "Stream video tokens retrieved successfully"
}
```

## Call Management Endpoints

### 3. Initiate Call

**Endpoint:** `POST /api/calls/initiate`

**Description:** Initiate a new voice or video call.

**Request Body:**
```json
{
  "receiver_id": 123,
  "type": "video"
}
```

**Parameters:**
- `receiver_id` (required): ID of the user to call
- `type` (required): Call type - "audio" or "video"

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "caller_id": 789,
    "receiver_id": 123,
    "call_type": "video",
    "status": "ringing",
    "started_at": "2025-07-15T10:30:00.000000Z",
    "caller": {
      "id": 789,
      "name": "John Doe",
      "avatar_url": "https://example.com/avatar.jpg"
    },
    "receiver": {
      "id": 123,
      "name": "Jane Smith",
      "avatar_url": "https://example.com/avatar2.jpg"
    },
    "stream_tokens": {
      "caller_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "receiver_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "api_key": "your_stream_api_key",
      "expires_at": "2025-07-16T10:30:00.000000Z"
    }
  },
  "message": "Call initiated successfully"
}
```

**Note:** For video calls, Stream video tokens are automatically generated and included in the response for both caller and receiver.

**Broadcast Event:** `CallInitiated` event is sent to `call.{receiver_id}` channel.

### 4. Answer Call

**Endpoint:** `POST /api/calls/{call_id}/answer`

**Description:** Accept an incoming call.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "status": "answered",
    "answered_at": "2025-07-15T10:31:00.000000Z"
  },
  "message": "Call answered successfully"
}
```

**Broadcast Event:** `CallAccepted` event is sent to both `call.{caller_id}` and `call.{receiver_id}` channels.

### 5. End Call

**Endpoint:** `POST /api/calls/{call_id}/end`

**Description:** End an active call.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "status": "ended",
    "ended_at": "2025-07-15T10:35:00.000000Z",
    "duration": 240
  },
  "message": "Call ended successfully"
}
```

**Broadcast Event:** `CallEnded` event is sent to both `call.{caller_id}` and `call.{receiver_id}` channels.

### 6. Reject Call

**Endpoint:** `POST /api/calls/{call_id}/decline`

**Description:** Reject an incoming call.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "status": "declined",
    "ended_at": "2025-07-15T10:30:30.000000Z"
  },
  "message": "Call declined successfully"
}
```

**Broadcast Event:** `CallRejected` event is sent to `call.{caller_id}` channel.

### 7. Get Call History

**Endpoint:** `GET /api/calls`

**Description:** Get call history for the authenticated user.

**Query Parameters:**
- `per_page` (optional): Number of calls per page (default: 50)
- `type` (optional): Filter by call type - "audio" or "video"

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 456,
        "caller_id": 789,
        "callee_id": 123,
        "type": "video",
        "status": "ended",
        "duration": 240,
        "started_at": "2025-07-15T10:30:00.000000Z",
        "ended_at": "2025-07-15T10:35:00.000000Z",
        "caller": {
          "id": 789,
          "name": "John Doe",
          "avatar": "https://example.com/avatar.jpg"
        },
        "callee": {
          "id": 123,
          "name": "Jane Smith",
          "avatar": "https://example.com/avatar2.jpg"
        }
      }
    ],
    "current_page": 1,
    "per_page": 50,
    "total": 1
  },
  "message": "Call history retrieved successfully"
}
```

### 8. Get Active Calls

**Endpoint:** `GET /api/calls/active`

**Description:** Get currently active calls for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "caller_id": 789,
      "callee_id": 123,
      "type": "video",
      "status": "answered",
      "started_at": "2025-07-15T10:30:00.000000Z",
      "answered_at": "2025-07-15T10:31:00.000000Z",
      "caller": {
        "id": 789,
        "name": "John Doe",
        "avatar": "https://example.com/avatar.jpg"
      },
      "callee": {
        "id": 123,
        "name": "Jane Smith",
        "avatar": "https://example.com/avatar2.jpg"
      }
    }
  ],
  "message": "Active calls retrieved successfully"
}
```

### 9. Get Call Statistics

**Endpoint:** `GET /api/calls/statistics`

**Description:** Get call statistics for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_calls": 25,
    "outgoing_calls": 15,
    "incoming_calls": 10,
    "answered_calls": 20,
    "missed_calls": 3,
    "total_talk_time": 3600,
    "total_talk_time_formatted": "1h 0m 0s",
    "video_calls": 12,
    "audio_calls": 13
  },
  "message": "Call statistics retrieved successfully"
}
```

## WebSocket Events

### Channel Subscription

Subscribe to private channel: `call.{user_id}`

**Authentication Required:** Yes (Bearer token)

### Event: CallInitiated

**Triggered when:** Someone initiates a call to you

**Event Data:**
```json
{
  "event_type": "call_initiated",
  "call_id": "456",
  "caller_id": "789",
  "recipient_id": "123",
  "caller_name": "John Doe",
  "caller_avatar": "https://example.com/avatar.jpg",
  "call_type": "video",
  "timestamp": "2025-07-15T10:30:00.000000Z",
  "metadata": {
    "chat_id": 101,
    "status": "ringing",
    "started_at": "2025-07-15T10:30:00.000000Z"
  }
}
```

### Event: CallAccepted

**Triggered when:** A call is accepted by the recipient

**Event Data:**
```json
{
  "event_type": "call_accepted",
  "call_id": "456",
  "caller_id": "789",
  "recipient_id": "123",
  "caller_name": "John Doe",
  "caller_avatar": "https://example.com/avatar.jpg",
  "call_type": "video",
  "timestamp": "2025-07-15T10:31:00.000000Z",
  "metadata": {
    "chat_id": 101,
    "status": "answered",
    "answered_at": "2025-07-15T10:31:00.000000Z"
  }
}
```

### Event: CallEnded

**Triggered when:** A call is ended by either party

**Event Data:**
```json
{
  "event_type": "call_ended",
  "call_id": "456",
  "caller_id": "789",
  "recipient_id": "123",
  "caller_name": "John Doe",
  "caller_avatar": "https://example.com/avatar.jpg",
  "call_type": "video",
  "timestamp": "2025-07-15T10:35:00.000000Z",
  "metadata": {
    "chat_id": 101,
    "status": "ended",
    "duration": 240,
    "ended_at": "2025-07-15T10:35:00.000000Z",
    "started_at": "2025-07-15T10:30:00.000000Z"
  }
}
```

### Event: CallRejected

**Triggered when:** A call is rejected by the recipient

**Event Data:**
```json
{
  "event_type": "call_rejected",
  "call_id": "456",
  "caller_id": "789",
  "recipient_id": "123",
  "caller_name": "John Doe",
  "caller_avatar": "https://example.com/avatar.jpg",
  "call_type": "video",
  "timestamp": "2025-07-15T10:30:30.000000Z",
  "metadata": {
    "chat_id": 101,
    "status": "declined",
    "ended_at": "2025-07-15T10:30:30.000000Z"
  }
}
```
