# WebSocket Implementation Guide

## Overview

This Laravel chat application now uses **Laravel Reverb** for real-time WebSocket functionality, replacing the previous Pusher integration. Laravel Reverb is Laravel's official WebSocket server that provides real-time broadcasting capabilities.

## Features Implemented

### 1. Real-time Messaging
- Instant message delivery to all chat participants
- Message read receipts
- Message delivery confirmations

### 2. User Presence
- Online/offline status tracking
- Last seen timestamps
- Real-time status updates

### 3. Typing Indicators
- Real-time typing status in chats
- Automatic typing timeout handling

### 4. WebSocket Events
- `message.sent` - New message broadcast
- `message.read` - Message read confirmation
- `user.typing` - Typing indicator
- `user.status.changed` - Online status changes

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=reverb

# Reverb WebSocket Server Configuration
REVERB_APP_ID=chatapp
REVERB_APP_KEY=chatapp-key
REVERB_APP_SECRET=chatapp-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Server Configuration

The WebSocket server is configured in `config/reverb.php`. Key settings:

- **Host**: `0.0.0.0` (accepts connections from any IP)
- **Port**: `8080` (default WebSocket port)
- **Scaling**: Redis-based scaling support for multiple server instances

## Starting the WebSocket Server

### Method 1: Using Artisan Command
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### Method 2: Using Provided Scripts

**Windows:**
```cmd
start-websocket-server.bat
```

**Linux/Mac:**
```bash
chmod +x start-websocket-server.sh
./start-websocket-server.sh
```

### Method 3: Using Custom Command
```bash
php artisan websocket:serve --host=0.0.0.0 --port=8080
```

## API Endpoints

### WebSocket Management

#### Get Connection Info
```http
GET /api/websocket/connection-info
```

Returns WebSocket connection details for frontend clients.

#### Update Online Status
```http
POST /api/websocket/online-status
Content-Type: application/json

{
    "is_online": true
}
```

#### Send Typing Indicator
```http
POST /api/websocket/chats/{chatId}/typing
Content-Type: application/json

{
    "is_typing": true
}
```

#### Mark Message as Read
```http
POST /api/websocket/messages/{messageId}/read
```

#### Get Active Chats
```http
GET /api/websocket/active-chats
```

Returns user's active chats for WebSocket subscription.

## WebSocket Channels

### Private Channels

#### User Channel
- **Channel**: `private-user.{userId}`
- **Purpose**: Personal notifications and direct messages
- **Authorization**: User must own the channel

#### Chat Channel
- **Channel**: `private-chat.{chatId}`
- **Purpose**: Chat messages and updates
- **Authorization**: User must be a chat participant

### Presence Channels

#### Chat Presence
- **Channel**: `presence-chat.{chatId}`
- **Purpose**: Typing indicators and participant presence
- **Authorization**: User must be a chat participant

#### Global Presence
- **Channel**: `presence-users`
- **Purpose**: Global user online status
- **Authorization**: Any authenticated user

## Frontend Integration

### JavaScript Example

```javascript
// Initialize WebSocket connection
const echo = new Echo({
    broadcaster: 'reverb',
    key: 'chatapp-key',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${userToken}`,
        },
    },
});

// Listen for new messages
echo.private(`chat.${chatId}`)
    .listen('message.sent', (e) => {
        console.log('New message:', e.message);
        // Update UI with new message
    });

// Listen for typing indicators
echo.join(`presence-chat.${chatId}`)
    .listen('user.typing', (e) => {
        console.log('User typing:', e.user.name, e.is_typing);
        // Show/hide typing indicator
    });

// Listen for user status changes
echo.join('presence-users')
    .listen('user.status.changed', (e) => {
        console.log('User status:', e.user.name, e.user.is_online);
        // Update user online status in UI
    });
```

## Maintenance Commands

### Cleanup Stale Connections
```bash
php artisan websocket:cleanup
```

### Schedule Regular Cleanup
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('websocket:cleanup')->everyFiveMinutes();
}
```

## Production Deployment

### 1. Process Management
Use a process manager like Supervisor to keep the WebSocket server running:

```ini
[program:laravel-websocket]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/websocket.log
```

### 2. Nginx Configuration
```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

### 3. SSL/TLS Configuration
For production, update your `.env`:

```env
REVERB_SCHEME=https
REVERB_PORT=443
```

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - Ensure WebSocket server is running
   - Check firewall settings for port 8080
   - Verify host/port configuration

2. **Authentication Errors**
   - Check broadcasting auth routes
   - Verify Sanctum token is valid
   - Ensure proper CORS configuration

3. **Messages Not Broadcasting**
   - Check queue worker is running
   - Verify event implements ShouldBroadcast
   - Check Redis connection if using scaling

### Debug Mode
Start server with debug flag:
```bash
php artisan reverb:start --debug
```

### Logs
Check WebSocket logs in:
- `storage/logs/laravel.log`
- WebSocket server console output

## Performance Considerations

### Scaling
- Enable Redis scaling for multiple server instances
- Use load balancer for WebSocket connections
- Monitor connection limits and memory usage

### Optimization
- Implement connection pooling
- Use message queues for heavy operations
- Cache frequently accessed data

## Security

### Authentication
- All channels require authentication
- Use Sanctum tokens for API access
- Implement proper channel authorization

### Rate Limiting
- Implement rate limiting for WebSocket events
- Monitor for abuse and spam
- Use proper input validation

## Migration from Pusher

The application has been successfully migrated from Pusher to Laravel Reverb:

1. ✅ Removed Pusher dependencies
2. ✅ Added Laravel Reverb
3. ✅ Updated broadcasting configuration
4. ✅ Created WebSocket events and channels
5. ✅ Added management API endpoints
6. ✅ Created startup scripts and documentation

All existing functionality is preserved while gaining the benefits of a self-hosted WebSocket solution.
