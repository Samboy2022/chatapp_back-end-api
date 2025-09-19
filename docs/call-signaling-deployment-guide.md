# Call Signaling Deployment Guide

## Overview

This guide covers deployment and configuration of the Laravel backend call signaling system for production environments.

## Prerequisites

- Laravel 10+ application
- MySQL/PostgreSQL database
- Redis for caching and queues
- Web server (Nginx/Apache)
- SSL certificate for HTTPS
- Pusher Cloud account OR Laravel Reverb setup

## Environment Configuration

### 1. Environment Variables

Add these variables to your `.env` file:

```env
# App Configuration
APP_NAME="FarmersNetwork"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue Configuration
QUEUE_CONNECTION=redis

# Broadcasting Configuration (Choose one)
BROADCAST_DRIVER=pusher
# OR
BROADCAST_DRIVER=reverb

# Pusher Configuration (if using Pusher)
PUSHER_APP_ID=2012149
PUSHER_APP_KEY=b3652bc3e7cddc5d6f80
PUSHER_APP_SECRET=a58bf3bdccfb58ded089
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Laravel Reverb Configuration (if using Reverb)
REVERB_APP_ID=local
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=6001
REVERB_SCHEME=ws

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=redis

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Database Migration

Run the database migrations:

```bash
# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed

# Create admin user
php artisan make:command CreateAdminUser
php artisan admin:create
```

### 3. Queue Configuration

Set up queue workers for handling broadcast events:

```bash
# Install supervisor for queue management
sudo apt-get install supervisor

# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Supervisor configuration:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Web Server Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    root /path/to/your/app/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Laravel Configuration
    index index.php;
    charset utf-8;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # WebSocket Proxy (for Laravel Reverb)
    location /ws {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Optimize static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Broadcasting Setup

### Option 1: Pusher Cloud Setup

1. **Create Pusher Account**: Sign up at https://pusher.com
2. **Create App**: Create a new app in Pusher dashboard
3. **Get Credentials**: Copy App ID, Key, Secret, and Cluster
4. **Configure Laravel**: Update `.env` with Pusher credentials
5. **Test Connection**: Use admin panel to test Pusher connection

### Option 2: Laravel Reverb Setup

1. **Install Reverb**: 
```bash
composer require laravel/reverb
php artisan reverb:install
```

2. **Configure Reverb**: Update `.env` with Reverb settings

3. **Start Reverb Server**:
```bash
# For development
php artisan reverb:start

# For production (use supervisor)
```

4. **Supervisor Configuration for Reverb**:
```ini
[program:laravel-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan reverb:start --host=0.0.0.0 --port=6001
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/reverb.log
```

## Security Configuration

### 1. API Rate Limiting

Configure rate limiting in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

protected $routeMiddleware = [
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];
```

Update `config/sanctum.php`:

```php
'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    'throttle:api' => \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
],
```

### 2. CORS Configuration

Update `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'broadcasting/auth'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://your-domain.com',
        'https://your-mobile-app-domain.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 3. Sanctum Configuration

Update `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,your-domain.com',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),

'expiration' => 60 * 24 * 7, // 7 days
```

## Performance Optimization

### 1. Caching Configuration

```bash
# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache when needed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Database Optimization

Add indexes for call queries:

```sql
-- Add indexes for better performance
CREATE INDEX idx_calls_caller_id ON calls(caller_id);
CREATE INDEX idx_calls_receiver_id ON calls(receiver_id);
CREATE INDEX idx_calls_status ON calls(status);
CREATE INDEX idx_calls_created_at ON calls(created_at);
CREATE INDEX idx_calls_call_type ON calls(call_type);

-- Composite indexes for common queries
CREATE INDEX idx_calls_status_created ON calls(status, created_at);
CREATE INDEX idx_calls_caller_status ON calls(caller_id, status);
CREATE INDEX idx_calls_receiver_status ON calls(receiver_id, status);
```

### 3. Redis Optimization

Configure Redis for optimal performance:

```redis
# redis.conf optimizations
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## Monitoring and Logging

### 1. Application Monitoring

Install Laravel Telescope for debugging:

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. Log Configuration

Update `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'call_signaling' => [
        'driver' => 'daily',
        'path' => storage_path('logs/call-signaling.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
],
```

### 3. Health Checks

Create health check endpoints:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'services' => [
            'database' => DB::connection()->getPdo() ? 'ok' : 'error',
            'redis' => Redis::ping() ? 'ok' : 'error',
            'queue' => Queue::size() !== false ? 'ok' : 'error',
        ]
    ]);
});
```

## Backup and Recovery

### 1. Database Backup

```bash
#!/bin/bash
# backup-database.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p database_name > /backups/db_backup_$DATE.sql
gzip /backups/db_backup_$DATE.sql

# Keep only last 7 days of backups
find /backups -name "db_backup_*.sql.gz" -mtime +7 -delete
```

### 2. Application Backup

```bash
#!/bin/bash
# backup-application.sh
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf /backups/app_backup_$DATE.tar.gz \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    /path/to/your/app

# Keep only last 7 days of backups
find /backups -name "app_backup_*.tar.gz" -mtime +7 -delete
```

## Deployment Checklist

### Pre-Deployment

- [ ] Environment variables configured
- [ ] Database migrations tested
- [ ] SSL certificate installed
- [ ] Web server configuration updated
- [ ] Queue workers configured
- [ ] Broadcasting service configured (Pusher/Reverb)
- [ ] Security headers configured
- [ ] Rate limiting configured
- [ ] CORS configured properly
- [ ] Backup system in place

### Post-Deployment

- [ ] Application accessible via HTTPS
- [ ] Admin panel accessible
- [ ] API endpoints responding correctly
- [ ] WebSocket connections working
- [ ] Queue workers running
- [ ] Broadcasting events working
- [ ] Real-time admin panel updating
- [ ] Mobile app can connect
- [ ] Call signaling working end-to-end
- [ ] Performance monitoring active
- [ ] Logs being written correctly
- [ ] Health checks passing

### Production Testing

- [ ] Test call initiation between users
- [ ] Test call acceptance and rejection
- [ ] Test call ending from both sides
- [ ] Test admin panel real-time features
- [ ] Test admin call termination
- [ ] Test WebSocket reconnection
- [ ] Test driver switching (if applicable)
- [ ] Load test with multiple concurrent calls
- [ ] Test mobile app integration
- [ ] Verify all events are delivered correctly

## Troubleshooting

### Common Issues

1. **WebSocket Connection Fails**
   - Check firewall settings
   - Verify SSL certificate covers WebSocket domain
   - Check Nginx proxy configuration

2. **Queue Jobs Not Processing**
   - Verify supervisor is running
   - Check Redis connection
   - Review worker logs

3. **Broadcasting Events Not Delivered**
   - Check Pusher/Reverb credentials
   - Verify channel authentication
   - Review Laravel logs

4. **High Memory Usage**
   - Optimize Redis configuration
   - Review queue job memory usage
   - Check for memory leaks in long-running processes

5. **Database Performance Issues**
   - Add missing indexes
   - Optimize slow queries
   - Consider database connection pooling

## Maintenance

### Regular Tasks

- Monitor application logs daily
- Check queue worker status
- Review database performance
- Update SSL certificates before expiry
- Apply security updates
- Monitor disk space usage
- Review backup integrity
- Test disaster recovery procedures

### Monthly Tasks

- Review and rotate logs
- Update dependencies
- Performance optimization review
- Security audit
- Backup system testing
- Documentation updates
