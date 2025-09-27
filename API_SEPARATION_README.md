# API and Web Separation Guide

This document explains how to properly separate API and Web routes in your Laravel application.

## Current Setup

### Web Server (Port 8000)
- **URL**: `http://localhost:8000`
- **Routes**: `routes/web.php`
- **Purpose**: Admin panel, landing pages, web interface
- **Middleware**: Web middleware, sessions, CSRF protection

### API Server (Port 8001)
- **URL**: `http://localhost:8001`
- **Routes**: `routes/api-only.php`
- **Purpose**: Mobile app API, JSON-only responses
- **Middleware**: API middleware, no sessions, JSON-only

## Files Created/Modified

### New Files
- `app/Http/Middleware/ApiResponseMiddleware.php` - Forces JSON responses
- `routes/api-only.php` - Dedicated API routes
- `start-api-server.bat` - API server startup script
- `API_SEPARATION_README.md` - This documentation

### Modified Files
- `bootstrap/app.php` - Added API middleware registration

## How to Use

### Start Web Server (Current)
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Start API Server (New)
```bash
# Double-click start-api-server.bat
# OR run manually:
php artisan serve --host=0.0.0.0 --port=8001
```

## API Endpoints (Port 8001)

### Health & Status
- `GET /api/health` - API health check
- `GET /api/status` - Server status
- `GET /api/public/test` - Public API test

### Authentication
- `POST /api/public/auth/register` - User registration
- `POST /api/public/auth/login` - User login
- `GET /api/auth/user` - Get current user (requires auth)

### Main API Routes
- `/api/chats/*` - Chat management
- `/api/messages/*` - Message handling
- `/api/calls/*` - Call management
- `/api/contacts/*` - Contact management
- `/api/status/*` - Status updates
- `/api/settings/*` - User settings
- `/api/broadcast-settings/*` - WebSocket configuration

## Benefits of This Separation

### 1. **No Route Conflicts**
- API routes never conflict with web routes
- Each server handles only its specific purpose

### 2. **Performance**
- API server has no web middleware overhead
- Web server has no API middleware overhead
- Better resource utilization

### 3. **Security**
- API server can't accidentally serve web content
- Web server can't accidentally serve API data
- Clear separation of concerns

### 4. **Scalability**
- Can deploy API and web servers separately
- Can scale API server independently
- Different caching strategies per server

### 5. **Development**
- Clear API-only testing environment
- No browser caching issues
- Better debugging experience

## Environment Configuration

### For API Server (.env)
```env
APP_ENV=api
API_PORT=8001
API_HOST=0.0.0.0
```

### For Web Server (.env)
```env
APP_ENV=web
WEB_PORT=8000
WEB_HOST=0.0.0.0
```

## Production Deployment

### Option 1: Separate Servers
- **API Server**: `api.yourdomain.com` or `yourdomain.com:8001`
- **Web Server**: `www.yourdomain.com` or `admin.yourdomain.com`

### Option 2: Reverse Proxy
```nginx
# Nginx configuration example
server {
    listen 80;
    server_name api.yourdomain.com;

    location / {
        proxy_pass http://localhost:8001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## Testing

### API Server Testing
```bash
# Test API health
curl http://localhost:8001/api/health

# Test API auth endpoint
curl http://localhost:8001/api/auth/user \
  -H "Accept: application/json"

# Test public API
curl http://localhost:8001/api/public/test
```

### Web Server Testing
```bash
# Test web routes
curl http://localhost:8000/
curl http://localhost:8000/admin
```

## Troubleshooting

### Issue: API returns HTML instead of JSON
**Solution**: Make sure you're accessing the correct port (8001 for API)

### Issue: Web routes not working
**Solution**: Make sure you're accessing the correct port (8000 for web)

### Issue: CORS errors
**Solution**: Configure CORS in your API server's `.env`:
```env
SANCTUM_STATEFUL_DOMAINS=localhost:8001,127.0.0.1:8001
CORS_ALLOWED_ORIGINS="http://localhost:8001,http://127.0.0.1:8001"
```

## Next Steps

1. **Test both servers** to ensure they work independently
2. **Configure CORS** for your specific frontend domains
3. **Set up proper environment files** for each server
4. **Configure production deployment** with reverse proxy
5. **Update your mobile app** to use the API server URL

## API-Only Server Features

- ✅ JSON-only responses
- ✅ No session middleware
- ✅ No CSRF protection (not needed for API)
- ✅ Optimized for mobile app consumption
- ✅ Clear API endpoint structure
- ✅ Comprehensive error handling
- ✅ Health check endpoints
- ✅ Proper HTTP status codes