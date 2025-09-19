# Broadcast Settings System - Production Deployment Guide

## 🚀 System Overview

The enhanced broadcast settings system provides dynamic WebSocket configuration management with:

- **Admin Control Panel**: Enable/disable broadcasting and configure drivers
- **API Endpoints**: Mobile apps can fetch current configuration dynamically
- **Driver Support**: Pusher Cloud and Laravel Reverb
- **Real-time Updates**: No app restarts required for configuration changes

## 📋 Pre-Deployment Checklist

### ✅ Database Migration
```bash
# Run the migration to create realtime_settings table
php artisan migrate

# Verify tables exist
php artisan tinker --execute="echo Schema::hasTable('realtime_settings') ? 'OK' : 'MISSING'"
```

### ✅ Environment Configuration
Ensure your `.env` file has the broadcast settings:
```env
# Pusher Cloud Configuration
PUSHER_APP_ID=2012149
PUSHER_APP_KEY=b3652bc3e7cddc5d6f80
PUSHER_APP_SECRET=a58bf3bdccfb58ded089
PUSHER_APP_CLUSTER=mt1

# Laravel Reverb Configuration (if using)
REVERB_APP_ID=chatapp
REVERB_APP_KEY=chatapp-key
REVERB_APP_SECRET=chatapp-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### ✅ Service Provider Registration
Ensure `AppServiceProvider` is properly configured to load broadcast settings on boot.

## 🎛️ Admin Panel Usage

### Access the Admin Panel
```
URL: /admin/realtime-settings
```

**Note**: The old `/admin/broadcast-settings` URL will automatically redirect to the new location.

### Configuration Options

#### 1. **Broadcasting Status**
- **Enabled**: Real-time features work normally
- **Disabled**: All broadcasting uses log driver (no real-time features)

#### 2. **Driver Selection**
- **Pusher**: Uses Pusher Cloud service
- **Reverb**: Uses self-hosted Laravel Reverb server

#### 3. **Pusher Cloud Settings**
- App ID: Your Pusher application ID
- App Key: Public key for client connections
- App Secret: Private key for server authentication
- Cluster: Geographic cluster (e.g., mt1, us2, eu)

#### 4. **Laravel Reverb Settings**
- App ID: Custom application identifier
- App Key: Public key for client connections
- App Secret: Private key for server authentication
- Host: Server hostname or IP address
- Port: WebSocket server port (default: 8080)
- Scheme: http or https

## 📱 Mobile App Integration

### API Endpoints for Mobile Apps

#### Get Current Configuration
```javascript
// Fetch current broadcast settings
const response = await fetch('/api/broadcast-settings');
const config = await response.json();

if (config.success && config.data.enabled) {
  // Initialize WebSocket with config.data.config
  await initializeWebSocket(config.data.config);
}
```

#### Check Status (Lightweight)
```javascript
// Quick status check for polling
const status = await fetch('/api/broadcast-settings/status');
const data = await status.json();

if (data.data.enabled !== currentlyEnabled) {
  // Configuration changed, reinitialize
  await reinitializeWebSocket();
}
```

#### Health Check
```javascript
// Health check with connection test
const health = await fetch('/api/broadcast-settings/health');
const healthData = await health.json();

console.log('Broadcasting health:', healthData.data.status);
```

### React Native Implementation Example
```javascript
import BroadcastConfigService from './services/BroadcastConfigService';
import WebSocketManager from './services/WebSocketManager';

// In your app initialization
useEffect(() => {
  const initializeBroadcasting = async () => {
    const config = await BroadcastConfigService.getConfig();
    
    if (config.enabled) {
      await WebSocketManager.initialize();
    }
  };
  
  initializeBroadcasting();
  
  // Check for updates every 5 minutes
  const interval = setInterval(async () => {
    const config = await BroadcastConfigService.getConfig(true);
    // Handle configuration changes
  }, 5 * 60 * 1000);
  
  return () => clearInterval(interval);
}, []);
```

## 🔧 Production Configuration

### Recommended Settings

#### For High-Traffic Applications
- **Driver**: Pusher Cloud (handles scaling automatically)
- **Monitoring**: Use health check endpoint for monitoring
- **Caching**: Configuration is cached for 5 minutes

#### For Self-Hosted Solutions
- **Driver**: Laravel Reverb
- **Server**: Dedicated server for WebSocket handling
- **SSL**: Use HTTPS scheme for production
- **Monitoring**: Monitor Reverb server health

### Performance Considerations

#### API Caching
The system caches configuration for 5 minutes to reduce database load:
```php
// Configuration is cached automatically
$settings = RealtimeSetting::current(); // Uses cache
```

#### Mobile App Caching
Mobile apps should cache configuration locally:
```javascript
// Cache configuration in AsyncStorage
await AsyncStorage.setItem('broadcast_config', JSON.stringify(config));
```

## 🚨 Troubleshooting

### Common Issues

#### 1. **Connection Test Fails**
- **Cause**: Network connectivity or DNS issues
- **Solution**: Configuration validation still passes; actual WebSocket connections may work
- **Check**: Verify credentials are correct in admin panel

#### 2. **Mobile App Not Connecting**
- **Check**: `/api/broadcast-settings/status` returns `enabled: true`
- **Verify**: Mobile app is using correct API endpoint
- **Debug**: Check mobile app logs for WebSocket connection errors

#### 3. **Configuration Not Updating**
- **Clear Cache**: Configuration is cached for 5 minutes
- **Restart**: Restart Laravel application if needed
- **Check**: Database has correct values in `realtime_settings` table

#### 4. **Pusher Authentication Errors**
- **Verify**: App ID, Key, and Secret are correct
- **Check**: Cluster setting matches your Pusher app
- **Confirm**: Pusher app is active and not suspended

### Debug Commands

#### Check Current Configuration
```bash
php artisan tinker --execute="
\$settings = App\Models\RealtimeSetting::current();
echo 'Status: ' . \$settings->status . PHP_EOL;
echo 'Driver: ' . \$settings->driver . PHP_EOL;
echo 'Pusher App ID: ' . \$settings->pusher_app_id . PHP_EOL;
"
```

#### Test Connection
```bash
php artisan tinker --execute="
\$test = App\Models\RealtimeSetting::testConnection();
echo 'Success: ' . (\$test['success'] ? 'YES' : 'NO') . PHP_EOL;
echo 'Message: ' . \$test['message'] . PHP_EOL;
"
```

## 📊 Monitoring

### Health Check Endpoint
```bash
# Monitor broadcasting health
curl http://your-domain.com/api/broadcast-settings/health

# Expected response for healthy system:
{
  "success": true,
  "data": {
    "status": "healthy",
    "broadcast_enabled": true,
    "driver": "pusher",
    "connection": true,
    "message": "Pusher Cloud configuration valid and connectivity OK",
    "timestamp": "2025-07-14T19:33:57.000000Z"
  }
}
```

### Log Monitoring
Monitor Laravel logs for broadcast-related messages:
```bash
tail -f storage/logs/laravel.log | grep -i broadcast
```

## 🔄 Backup and Recovery

### Database Backup
```sql
-- Backup realtime_settings table
SELECT * FROM realtime_settings;

-- Backup broadcast_settings table (if used)
SELECT * FROM broadcast_settings WHERE key LIKE 'broadcast_%';
```

### Configuration Export
The admin panel provides export functionality for configuration backup.

## 🎯 Best Practices

1. **Test Before Deployment**: Always test configuration changes in staging
2. **Monitor Health**: Use health check endpoint for monitoring
3. **Cache Appropriately**: Don't over-poll the API from mobile apps
4. **Handle Failures Gracefully**: Mobile apps should work offline with cached config
5. **Security**: Keep API secrets secure and rotate them regularly
6. **Documentation**: Keep this guide updated with any customizations

## 📞 Support

For issues with the broadcast settings system:

1. Check the troubleshooting section above
2. Review Laravel logs for error messages
3. Test API endpoints manually with curl
4. Verify database configuration is correct

The system is designed to be resilient and should handle most edge cases gracefully.
