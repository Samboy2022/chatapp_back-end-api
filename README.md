# ChatApp Backend API

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 📋 Overview

**ChatApp** is a comprehensive, enterprise-grade real-time messaging platform that replicates WhatsApp's core functionality. Built with Laravel 12.x, this backend API powers a feature-rich chat application with advanced administrative controls, dual broadcasting systems, and scalable architecture supporting React Native mobile apps.

### 🎯 Key Features

- **Real-time Messaging**: Instant message delivery with WebSocket support using Laravel Reverb or Pusher
- **Dual Broadcasting System**: Seamless switching between Pusher Cloud and Laravel Reverb
- **Multi-chat Support**: Private one-on-one and group chats (up to 256 members)
- **Rich Media Sharing**: Images, videos, documents, voice messages, and location sharing
- **Status Updates**: WhatsApp-style stories with 24-hour auto-expiration
- **Voice/Video Calling**: Full call management with history and statistics
- **Contact Management**: Sync contacts, block/unblock users, favorites
- **Message Reactions**: Emoji reactions and replies
- **Enterprise Admin Dashboard**: Comprehensive system administration
- **Advanced Search**: Search users, messages, and conversations
- **Privacy Controls**: Granular privacy settings and data protection
- **Offline Resilience**: Mobile app offline capability with sync

### 🏗️ Architecture

- **Backend**: Laravel 12.x with PHP 8.2+
- **Authentication**: Laravel Sanctum API tokens
- **Database**: MySQL 8.0+ with comprehensive migrations
- **Broadcasting**: Dual system (Pusher Cloud + Laravel Reverb)
- **Cache/Queue**: Redis for performance optimization
- **File Storage**: Local/AWS S3 with media processing
- **Mobile App**: React Native with Expo integration

## 🚀 Core Functionalities

### 💬 Messaging System
- **Message Types**: Text, images, videos, audio, documents, location, contacts
- **Advanced Features**: Reply to messages, reactions, forwarding, deletion
- **Read Receipts**: Delivery and read confirmations
- **Message Search**: Full-text search across conversations

### 👥 Chat Management
- **Private Chats**: One-on-one conversations
- **Group Chats**: Multi-participant groups with admin roles
- **Chat Settings**: Mute notifications, archive, pin chats
- **Participant Management**: Add/remove members, role assignment

### 📱 Status/Story System
- **24-Hour Expiration**: Automatic cleanup
- **Media Support**: Text, images, videos
- **Privacy Controls**: Everyone, contacts, close friends
- **View Tracking**: See who viewed your status

### 📞 Voice/Video Calling
- **Call Types**: Voice and video calls
- **Call Management**: Initiate, answer, reject, end calls
- **Call History**: Complete call logs with duration
- **Statistics**: Call analytics and reporting

### 🔍 Contact & Search
- **Contact Sync**: Phone contact synchronization
- **User Search**: Search by phone/email
- **Blocking System**: Block/unblock users
- **Favorites**: Mark important contacts

### 🖼️ Media Management
- **File Upload**: Drag-and-drop file uploading
- **Media Processing**: Image thumbnails, video compression
- **Storage**: Secure cloud storage with CDN
- **Gallery**: Media gallery per chat

### 🔐 Security & Privacy
- **End-to-End Encryption**: Message encryption capability
- **Privacy Settings**: Last seen, profile photo, about info
- **GDPR Compliance**: Data export and deletion rights
- **Rate Limiting**: API protection and abuse prevention

### ⚡ Real-time Features
- **Instant Delivery**: <100ms message delivery
- **Typing Indicators**: Real-time typing status
- **Online Presence**: User availability status
- **WebSocket Channels**: Private and presence channels

## 📚 API Endpoints

### Authentication
```
POST   /api/auth/register          - User registration
POST   /api/auth/login             - User login
POST   /api/auth/logout            - User logout
GET    /api/auth/user              - Get authenticated user
PUT    /api/auth/profile           - Update user profile
PUT    /api/auth/privacy           - Update privacy settings
POST   /api/auth/refresh           - Refresh authentication token
```

### Chats & Messages
```
GET    /api/chats                           - List user's chats
POST   /api/chats                           - Create new chat
GET    /api/chats/{chatId}                  - Get specific chat
PUT    /api/chats/{chatId}                  - Update chat settings
POST   /api/chats/{chatId}/archive          - Archive chat
POST   /api/chats/{chatId}/pin              - Pin chat
POST   /api/chats/{chatId}/mute             - Mute chat notifications
POST   /api/chats/{chatId}/leave            - Leave group chat

GET    /api/chats/{chatId}/messages         - Get chat messages
POST   /api/chats/{chatId}/messages         - Send message
PUT    /api/messages/{messageId}            - Edit message
DELETE /api/messages/{messageId}            - Delete message
POST   /api/messages/{messageId}/read       - Mark as read
POST   /api/messages/{messageId}/react      - Add reaction
DELETE /api/messages/{messageId}/react      - Remove reaction
```

### Group Management
```
GET    /api/groups                          - List user's groups
POST   /api/groups                          - Create group
GET    /api/groups/{groupId}                - Get group details
POST   /api/groups/{groupId}/members        - Add members
DELETE /api/groups/{groupId}/members/{id}   - Remove member
POST   /api/groups/{groupId}/leave          - Leave group
POST   /api/groups/{groupId}/message        - Send group message
```

### Status Updates
```
GET    /api/status                          - Get status updates
POST   /api/status                          - Create status
GET    /api/status/user/{userId}            - Get user's statuses
POST   /api/status/{statusId}/view          - Mark status as viewed
GET    /api/status/{statusId}/viewers       - Get status viewers
DELETE /api/status/{statusId}               - Delete status
```

### Voice/Video Calls
```
GET    /api/calls                           - List call history
GET    /api/calls/active                    - Get active calls
POST   /api/calls                           - Initiate call
POST   /api/calls/{callId}/accept           - Accept call
POST   /api/calls/{callId}/answer           - Answer call
POST   /api/calls/{callId}/reject           - Reject call
POST   /api/calls/{callId}/decline          - Decline call
POST   /api/calls/{callId}/end              - End call
GET    /api/calls/{callId}                  - Get call details
GET    /api/calls/statistics                - Get call statistics
GET    /api/calls/missed-count              - Get missed calls count
```

### Contact Management
```
GET    /api/contacts                        - List contacts
POST   /api/contacts/sync                   - Sync phone contacts
GET    /api/contacts/blocked                - Get blocked contacts
GET    /api/contacts/favorites              - Get favorite contacts
GET    /api/contacts/search                 - Search contacts
POST   /api/contacts/block/{contactId}      - Block contact
POST   /api/contacts/unblock/{contactId}    - Unblock contact
POST   /api/contacts/favorite/{contactId}   - Toggle favorite
```

### Media & Files
```
POST   /api/media/upload                    - Upload media file
POST   /api/media/upload/avatar             - Upload user avatar
POST   /api/media/upload/chat-avatar        - Upload chat avatar
POST   /api/media/upload/status             - Upload status media
DELETE /api/media/delete                    - Delete media file
```

### Search & Discovery
```
GET    /api/search/users                    - Search users
GET    /api/search/messages                 - Search messages
POST   /api/chats/create-or-get             - Create or get private chat
GET    /api/users/search                    - Search users (alternative)
GET    /api/users/search/phone              - Search by phone number
GET    /api/users/search/email              - Search by email
```

### User Settings
```
GET    /api/settings/profile                - Get user profile
POST   /api/settings/profile                - Update profile
GET    /api/settings/privacy                - Get privacy settings
POST   /api/settings/privacy                - Update privacy settings
GET    /api/settings/media-settings         - Get media settings
POST   /api/settings/media-settings         - Update media settings
GET    /api/settings/notifications          - Get notification settings
POST   /api/settings/notifications          - Update notification settings
POST   /api/settings/delete-account         - Delete account
GET    /api/settings/export-data            - Export user data
```

### WebSocket & Real-time
```
GET    /api/websocket/connection-info        - Get WebSocket connection info
GET    /api/websocket/active-chats          - Get active chat connections
POST   /api/websocket/online-status         - Update online status
POST   /api/websocket/chats/{chatId}/typing - Send typing indicator
POST   /api/websocket/messages/{id}/read    - Mark message as read
```

### Broadcasting & Configuration
```
GET    /api/broadcast-settings              - Get broadcast settings
GET    /api/broadcast-settings/connection-info - Get connection info
GET    /api/broadcast-settings/status       - Get broadcast status
GET    /api/broadcast-settings/health       - Health check
POST   /api/broadcast-settings/test         - Test connection
GET    /api/broadcast-settings/call-signaling - Get call signaling config

GET    /api/app-config                      - Get app configuration
GET    /api/app-config/validate             - Validate configuration
POST   /api/app-config/clear-cache          - Clear configuration cache
GET    /api/app-config/history              - Get configuration history

GET    /api/app-settings                    - Get app settings
GET    /api/app-settings/config             - Get app config
GET    /api/app-settings/version            - Get app version
```

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+ or PostgreSQL
- Redis (recommended)
- WebSocket server (Laravel Reverb or Pusher)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/chatapp-backend.git
   cd chatapp-backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Broadcasting Setup**
   ```bash
   # For Laravel Reverb
   php artisan reverb:install

   # For Pusher Cloud, configure in .env
   BROADCAST_DRIVER=pusher
   ```

7. **Storage Setup**
   ```bash
   php artisan storage:link
   ```

8. **Start the Application**
   ```bash
   # Start Laravel server
   php artisan serve

   # Start WebSocket server (in another terminal)
   php artisan reverb:start

   # Start queue worker (in another terminal)
   php artisan queue:work
   ```

### Production Deployment

1. **Web Server Configuration**
   - Nginx or Apache with PHP-FPM
   - SSL certificate configuration
   - Domain configuration

2. **Process Management**
   - Supervisor for queue workers
   - Systemd for WebSocket server
   - Load balancer configuration

3. **Monitoring**
   - Laravel Telescope for debugging
   - Server monitoring tools
   - Log aggregation

## 📊 Admin Dashboard

### Features
- **User Management**: View, block, manage users
- **Content Moderation**: Monitor and moderate messages
- **System Monitoring**: Health checks, performance metrics
- **Broadcasting Configuration**: Switch between Pusher/Reverb
- **Analytics**: Message volume, user statistics
- **Settings Management**: System configuration

### Access
Navigate to `/admin` in your browser and login with admin credentials.

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **Unit Tests**: Core functionality
- **Feature Tests**: API endpoints
- **Integration Tests**: End-to-end workflows
- **Performance Tests**: Load testing

## 📚 Documentation

- **[API Documentation](./docs/api-documentation/index.html)**: Complete API reference with examples
- **[Product Requirements Document](./COMPREHENSIVE_PRD_CHATAPP.md)**: Detailed system specifications
- **[Broadcast Configuration](./docs/broadcast-settings-complete-migration.md)**: Broadcasting setup guide
- **[Mobile Implementation](./docs/mobile-app-broadcast-integration.md)**: Mobile app integration guide

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME=ChatApp
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatapp
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=chatapp
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- React Native Community
- All contributors and testers

---

**Built with ❤️ for seamless communication**
>>>>>>> master
# ChatApp Backend API

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 📋 Overview

**ChatApp** is a comprehensive, enterprise-grade real-time messaging platform that replicates WhatsApp's core functionality. Built with Laravel 12.x, this backend API powers a feature-rich chat application with advanced administrative controls, dual broadcasting systems, and scalable architecture supporting React Native mobile apps.

### 🎯 Key Features

- **Real-time Messaging**: Instant message delivery with WebSocket support using Laravel Reverb or Pusher
- **Dual Broadcasting System**: Seamless switching between Pusher Cloud and Laravel Reverb
- **Multi-chat Support**: Private one-on-one and group chats (up to 256 members)
- **Rich Media Sharing**: Images, videos, documents, voice messages, and location sharing
- **Status Updates**: WhatsApp-style stories with 24-hour auto-expiration
- **Voice/Video Calling**: Full call management with history and statistics
- **Contact Management**: Sync contacts, block/unblock users, favorites
- **Message Reactions**: Emoji reactions and replies
- **Enterprise Admin Dashboard**: Comprehensive system administration
- **Advanced Search**: Search users, messages, and conversations
- **Privacy Controls**: Granular privacy settings and data protection
- **Offline Resilience**: Mobile app offline capability with sync

### 🏗️ Architecture

- **Backend**: Laravel 12.x with PHP 8.2+
- **Authentication**: Laravel Sanctum API tokens
- **Database**: MySQL 8.0+ with comprehensive migrations
- **Broadcasting**: Dual system (Pusher Cloud + Laravel Reverb)
- **Cache/Queue**: Redis for performance optimization
- **File Storage**: Local/AWS S3 with media processing
- **Mobile App**: React Native with Expo integration

## 🚀 Core Functionalities

### 💬 Messaging System
- **Message Types**: Text, images, videos, audio, documents, location, contacts
- **Advanced Features**: Reply to messages, reactions, forwarding, deletion
- **Read Receipts**: Delivery and read confirmations
- **Message Search**: Full-text search across conversations

### 👥 Chat Management
- **Private Chats**: One-on-one conversations
- **Group Chats**: Multi-participant groups with admin roles
- **Chat Settings**: Mute notifications, archive, pin chats
- **Participant Management**: Add/remove members, role assignment

### 📱 Status/Story System
- **24-Hour Expiration**: Automatic cleanup
- **Media Support**: Text, images, videos
- **Privacy Controls**: Everyone, contacts, close friends
- **View Tracking**: See who viewed your status

### 📞 Voice/Video Calling
- **Call Types**: Voice and video calls
- **Call Management**: Initiate, answer, reject, end calls
- **Call History**: Complete call logs with duration
- **Statistics**: Call analytics and reporting

### 🔍 Contact & Search
- **Contact Sync**: Phone contact synchronization
- **User Search**: Search by phone/email
- **Blocking System**: Block/unblock users
- **Favorites**: Mark important contacts

### 🖼️ Media Management
- **File Upload**: Drag-and-drop file uploading
- **Media Processing**: Image thumbnails, video compression
- **Storage**: Secure cloud storage with CDN
- **Gallery**: Media gallery per chat

### 🔐 Security & Privacy
- **End-to-End Encryption**: Message encryption capability
- **Privacy Settings**: Last seen, profile photo, about info
- **GDPR Compliance**: Data export and deletion rights
- **Rate Limiting**: API protection and abuse prevention

### ⚡ Real-time Features
- **Instant Delivery**: <100ms message delivery
- **Typing Indicators**: Real-time typing status
- **Online Presence**: User availability status
- **WebSocket Channels**: Private and presence channels

## 📚 API Endpoints

### Authentication
```
POST   /api/auth/register          - User registration
POST   /api/auth/login             - User login
POST   /api/auth/logout            - User logout
GET    /api/auth/user              - Get authenticated user
PUT    /api/auth/profile           - Update user profile
PUT    /api/auth/privacy           - Update privacy settings
POST   /api/auth/refresh           - Refresh authentication token
```

### Chats & Messages
```
GET    /api/chats                           - List user's chats
POST   /api/chats                           - Create new chat
GET    /api/chats/{chatId}                  - Get specific chat
PUT    /api/chats/{chatId}                  - Update chat settings
POST   /api/chats/{chatId}/archive          - Archive chat
POST   /api/chats/{chatId}/pin              - Pin chat
POST   /api/chats/{chatId}/mute             - Mute chat notifications
POST   /api/chats/{chatId}/leave            - Leave group chat

GET    /api/chats/{chatId}/messages         - Get chat messages
POST   /api/chats/{chatId}/messages         - Send message
PUT    /api/messages/{messageId}            - Edit message
DELETE /api/messages/{messageId}            - Delete message
POST   /api/messages/{messageId}/read       - Mark as read
POST   /api/messages/{messageId}/react      - Add reaction
DELETE /api/messages/{messageId}/react      - Remove reaction
```

### Group Management
```
GET    /api/groups                          - List user's groups
POST   /api/groups                          - Create group
GET    /api/groups/{groupId}                - Get group details
POST   /api/groups/{groupId}/members        - Add members
DELETE /api/groups/{groupId}/members/{id}   - Remove member
POST   /api/groups/{groupId}/leave          - Leave group
POST   /api/groups/{groupId}/message        - Send group message
```

### Status Updates
```
GET    /api/status                          - Get status updates
POST   /api/status                          - Create status
GET    /api/status/user/{userId}            - Get user's statuses
POST   /api/status/{statusId}/view          - Mark status as viewed
GET    /api/status/{statusId}/viewers       - Get status viewers
DELETE /api/status/{statusId}               - Delete status
```

### Voice/Video Calls
```
GET    /api/calls                           - List call history
GET    /api/calls/active                    - Get active calls
POST   /api/calls                           - Initiate call
POST   /api/calls/{callId}/accept           - Accept call
POST   /api/calls/{callId}/answer           - Answer call
POST   /api/calls/{callId}/reject           - Reject call
POST   /api/calls/{callId}/decline          - Decline call
POST   /api/calls/{callId}/end              - End call
GET    /api/calls/{callId}                  - Get call details
GET    /api/calls/statistics                - Get call statistics
GET    /api/calls/missed-count              - Get missed calls count
```

### Contact Management
```
GET    /api/contacts                        - List contacts
POST   /api/contacts/sync                   - Sync phone contacts
GET    /api/contacts/blocked                - Get blocked contacts
GET    /api/contacts/favorites              - Get favorite contacts
GET    /api/contacts/search                 - Search contacts
POST   /api/contacts/block/{contactId}      - Block contact
POST   /api/contacts/unblock/{contactId}    - Unblock contact
POST   /api/contacts/favorite/{contactId}   - Toggle favorite
```

### Media & Files
```
POST   /api/media/upload                    - Upload media file
POST   /api/media/upload/avatar             - Upload user avatar
POST   /api/media/upload/chat-avatar        - Upload chat avatar
POST   /api/media/upload/status             - Upload status media
DELETE /api/media/delete                    - Delete media file
```

### Search & Discovery
```
GET    /api/search/users                    - Search users
GET    /api/search/messages                 - Search messages
POST   /api/chats/create-or-get             - Create or get private chat
GET    /api/users/search                    - Search users (alternative)
GET    /api/users/search/phone              - Search by phone number
GET    /api/users/search/email              - Search by email
```

### User Settings
```
GET    /api/settings/profile                - Get user profile
POST   /api/settings/profile                - Update profile
GET    /api/settings/privacy                - Get privacy settings
POST   /api/settings/privacy                - Update privacy settings
GET    /api/settings/media-settings         - Get media settings
POST   /api/settings/media-settings         - Update media settings
GET    /api/settings/notifications          - Get notification settings
POST   /api/settings/notifications          - Update notification settings
POST   /api/settings/delete-account         - Delete account
GET    /api/settings/export-data            - Export user data
```

### WebSocket & Real-time
```
GET    /api/websocket/connection-info        - Get WebSocket connection info
GET    /api/websocket/active-chats          - Get active chat connections
POST   /api/websocket/online-status         - Update online status
POST   /api/websocket/chats/{chatId}/typing - Send typing indicator
POST   /api/websocket/messages/{id}/read    - Mark message as read
```

### Broadcasting & Configuration
```
GET    /api/broadcast-settings              - Get broadcast settings
GET    /api/broadcast-settings/connection-info - Get connection info
GET    /api/broadcast-settings/status       - Get broadcast status
GET    /api/broadcast-settings/health       - Health check
POST   /api/broadcast-settings/test         - Test connection
GET    /api/broadcast-settings/call-signaling - Get call signaling config

GET    /api/app-config                      - Get app configuration
GET    /api/app-config/validate             - Validate configuration
POST   /api/app-config/clear-cache          - Clear configuration cache
GET    /api/app-config/history              - Get configuration history

GET    /api/app-settings                    - Get app settings
GET    /api/app-settings/config             - Get app config
GET    /api/app-settings/version            - Get app version
```

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+ or PostgreSQL
- Redis (recommended)
- WebSocket server (Laravel Reverb or Pusher)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/chatapp-backend.git
   cd chatapp-backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Broadcasting Setup**
   ```bash
   # For Laravel Reverb
   php artisan reverb:install

   # For Pusher Cloud, configure in .env
   BROADCAST_DRIVER=pusher
   ```

7. **Storage Setup**
   ```bash
   php artisan storage:link
   ```

8. **Start the Application**
   ```bash
   # Start Laravel server
   php artisan serve

   # Start WebSocket server (in another terminal)
   php artisan reverb:start

   # Start queue worker (in another terminal)
   php artisan queue:work
   ```

### Production Deployment

1. **Web Server Configuration**
   - Nginx or Apache with PHP-FPM
   - SSL certificate configuration
   - Domain configuration

2. **Process Management**
   - Supervisor for queue workers
   - Systemd for WebSocket server
   - Load balancer configuration

3. **Monitoring**
   - Laravel Telescope for debugging
   - Server monitoring tools
   - Log aggregation

## 📊 Admin Dashboard

### Features
- **User Management**: View, block, manage users
- **Content Moderation**: Monitor and moderate messages
- **System Monitoring**: Health checks, performance metrics
- **Broadcasting Configuration**: Switch between Pusher/Reverb
- **Analytics**: Message volume, user statistics
- **Settings Management**: System configuration

### Access
Navigate to `/admin` in your browser and login with admin credentials.

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **Unit Tests**: Core functionality
- **Feature Tests**: API endpoints
- **Integration Tests**: End-to-end workflows
- **Performance Tests**: Load testing

## 📚 Documentation

- **[API Documentation](./docs/api-documentation/index.html)**: Complete API reference with examples
- **[Product Requirements Document](./COMPREHENSIVE_PRD_CHATAPP.md)**: Detailed system specifications
- **[Broadcast Configuration](./docs/broadcast-settings-complete-migration.md)**: Broadcasting setup guide
- **[Mobile Implementation](./docs/mobile-app-broadcast-integration.md)**: Mobile app integration guide

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME=ChatApp
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatapp
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=chatapp
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- React Native Community
- All contributors and testers

---

**Built with ❤️ for seamless communication**
=======
# ChatApp Backend API

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 📋 Overview

**ChatApp** is a comprehensive, enterprise-grade real-time messaging platform that replicates WhatsApp's core functionality. Built with Laravel 12.x, this backend API powers a feature-rich chat application with advanced administrative controls, dual broadcasting systems, and scalable architecture supporting React Native mobile apps.

### 🎯 Key Features

- **Real-time Messaging**: Instant message delivery with WebSocket support using Laravel Reverb or Pusher
- **Dual Broadcasting System**: Seamless switching between Pusher Cloud and Laravel Reverb
- **Multi-chat Support**: Private one-on-one and group chats (up to 256 members)
- **Rich Media Sharing**: Images, videos, documents, voice messages, and location sharing
- **Status Updates**: WhatsApp-style stories with 24-hour auto-expiration
- **Voice/Video Calling**: Full call management with history and statistics
- **Contact Management**: Sync contacts, block/unblock users, favorites
- **Message Reactions**: Emoji reactions and replies
- **Enterprise Admin Dashboard**: Comprehensive system administration
- **Advanced Search**: Search users, messages, and conversations
- **Privacy Controls**: Granular privacy settings and data protection
- **Offline Resilience**: Mobile app offline capability with sync

### 🏗️ Architecture

- **Backend**: Laravel 12.x with PHP 8.2+
- **Authentication**: Laravel Sanctum API tokens
- **Database**: MySQL 8.0+ with comprehensive migrations
- **Broadcasting**: Dual system (Pusher Cloud + Laravel Reverb)
- **Cache/Queue**: Redis for performance optimization
- **File Storage**: Local/AWS S3 with media processing
- **Mobile App**: React Native with Expo integration

## 🚀 Core Functionalities

### 💬 Messaging System
- **Message Types**: Text, images, videos, audio, documents, location, contacts
- **Advanced Features**: Reply to messages, reactions, forwarding, deletion
- **Read Receipts**: Delivery and read confirmations
- **Message Search**: Full-text search across conversations

### 👥 Chat Management
- **Private Chats**: One-on-one conversations
- **Group Chats**: Multi-participant groups with admin roles
- **Chat Settings**: Mute notifications, archive, pin chats
- **Participant Management**: Add/remove members, role assignment

### 📱 Status/Story System
- **24-Hour Expiration**: Automatic cleanup
- **Media Support**: Text, images, videos
- **Privacy Controls**: Everyone, contacts, close friends
- **View Tracking**: See who viewed your status

### 📞 Voice/Video Calling
- **Call Types**: Voice and video calls
- **Call Management**: Initiate, answer, reject, end calls
- **Call History**: Complete call logs with duration
- **Statistics**: Call analytics and reporting

### 🔍 Contact & Search
- **Contact Sync**: Phone contact synchronization
- **User Search**: Search by phone/email
- **Blocking System**: Block/unblock users
- **Favorites**: Mark important contacts

### 🖼️ Media Management
- **File Upload**: Drag-and-drop file uploading
- **Media Processing**: Image thumbnails, video compression
- **Storage**: Secure cloud storage with CDN
- **Gallery**: Media gallery per chat

### 🔐 Security & Privacy
- **End-to-End Encryption**: Message encryption capability
- **Privacy Settings**: Last seen, profile photo, about info
- **GDPR Compliance**: Data export and deletion rights
- **Rate Limiting**: API protection and abuse prevention

### ⚡ Real-time Features
- **Instant Delivery**: <100ms message delivery
- **Typing Indicators**: Real-time typing status
- **Online Presence**: User availability status
- **WebSocket Channels**: Private and presence channels

## 📚 API Endpoints

### Authentication
```
POST   /api/auth/register          - User registration
POST   /api/auth/login             - User login
POST   /api/auth/logout            - User logout
GET    /api/auth/user              - Get authenticated user
PUT    /api/auth/profile           - Update user profile
PUT    /api/auth/privacy           - Update privacy settings
POST   /api/auth/refresh           - Refresh authentication token
```

### Chats & Messages
```
GET    /api/chats                           - List user's chats
POST   /api/chats                           - Create new chat
GET    /api/chats/{chatId}                  - Get specific chat
PUT    /api/chats/{chatId}                  - Update chat settings
POST   /api/chats/{chatId}/archive          - Archive chat
POST   /api/chats/{chatId}/pin              - Pin chat
POST   /api/chats/{chatId}/mute             - Mute chat notifications
POST   /api/chats/{chatId}/leave            - Leave group chat

GET    /api/chats/{chatId}/messages         - Get chat messages
POST   /api/chats/{chatId}/messages         - Send message
PUT    /api/messages/{messageId}            - Edit message
DELETE /api/messages/{messageId}            - Delete message
POST   /api/messages/{messageId}/read       - Mark as read
POST   /api/messages/{messageId}/react      - Add reaction
DELETE /api/messages/{messageId}/react      - Remove reaction
```

### Group Management
```
GET    /api/groups                          - List user's groups
POST   /api/groups                          - Create group
GET    /api/groups/{groupId}                - Get group details
POST   /api/groups/{groupId}/members        - Add members
DELETE /api/groups/{groupId}/members/{id}   - Remove member
POST   /api/groups/{groupId}/leave          - Leave group
POST   /api/groups/{groupId}/message        - Send group message
```

### Status Updates
```
GET    /api/status                          - Get status updates
POST   /api/status                          - Create status
GET    /api/status/user/{userId}            - Get user's statuses
POST   /api/status/{statusId}/view          - Mark status as viewed
GET    /api/status/{statusId}/viewers       - Get status viewers
DELETE /api/status/{statusId}               - Delete status
```

### Voice/Video Calls
```
GET    /api/calls                           - List call history
GET    /api/calls/active                    - Get active calls
POST   /api/calls                           - Initiate call
POST   /api/calls/{callId}/accept           - Accept call
POST   /api/calls/{callId}/answer           - Answer call
POST   /api/calls/{callId}/reject           - Reject call
POST   /api/calls/{callId}/decline          - Decline call
POST   /api/calls/{callId}/end              - End call
GET    /api/calls/{callId}                  - Get call details
GET    /api/calls/statistics                - Get call statistics
GET    /api/calls/missed-count              - Get missed calls count
```

### Contact Management
```
GET    /api/contacts                        - List contacts
POST   /api/contacts/sync                   - Sync phone contacts
GET    /api/contacts/blocked                - Get blocked contacts
GET    /api/contacts/favorites              - Get favorite contacts
GET    /api/contacts/search                 - Search contacts
POST   /api/contacts/block/{contactId}      - Block contact
POST   /api/contacts/unblock/{contactId}    - Unblock contact
POST   /api/contacts/favorite/{contactId}   - Toggle favorite
```

### Media & Files
```
POST   /api/media/upload                    - Upload media file
POST   /api/media/upload/avatar             - Upload user avatar
POST   /api/media/upload/chat-avatar        - Upload chat avatar
POST   /api/media/upload/status             - Upload status media
DELETE /api/media/delete                    - Delete media file
```

### Search & Discovery
```
GET    /api/search/users                    - Search users
GET    /api/search/messages                 - Search messages
POST   /api/chats/create-or-get             - Create or get private chat
GET    /api/users/search                    - Search users (alternative)
GET    /api/users/search/phone              - Search by phone number
GET    /api/users/search/email              - Search by email
```

### User Settings
```
GET    /api/settings/profile                - Get user profile
POST   /api/settings/profile                - Update profile
GET    /api/settings/privacy                - Get privacy settings
POST   /api/settings/privacy                - Update privacy settings
GET    /api/settings/media-settings         - Get media settings
POST   /api/settings/media-settings         - Update media settings
GET    /api/settings/notifications          - Get notification settings
POST   /api/settings/notifications          - Update notification settings
POST   /api/settings/delete-account         - Delete account
GET    /api/settings/export-data            - Export user data
```

### WebSocket & Real-time
```
GET    /api/websocket/connection-info        - Get WebSocket connection info
GET    /api/websocket/active-chats          - Get active chat connections
POST   /api/websocket/online-status         - Update online status
POST   /api/websocket/chats/{chatId}/typing - Send typing indicator
POST   /api/websocket/messages/{id}/read    - Mark message as read
```

### Broadcasting & Configuration
```
GET    /api/broadcast-settings              - Get broadcast settings
GET    /api/broadcast-settings/connection-info - Get connection info
GET    /api/broadcast-settings/status       - Get broadcast status
GET    /api/broadcast-settings/health       - Health check
POST   /api/broadcast-settings/test         - Test connection
GET    /api/broadcast-settings/call-signaling - Get call signaling config

GET    /api/app-config                      - Get app configuration
GET    /api/app-config/validate             - Validate configuration
POST   /api/app-config/clear-cache          - Clear configuration cache
GET    /api/app-config/history              - Get configuration history

GET    /api/app-settings                    - Get app settings
GET    /api/app-settings/config             - Get app config
GET    /api/app-settings/version            - Get app version
```

## 🛠️ Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+ or PostgreSQL
- Redis (recommended)
- WebSocket server (Laravel Reverb or Pusher)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/chatapp-backend.git
   cd chatapp-backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Broadcasting Setup**
   ```bash
   # For Laravel Reverb
   php artisan reverb:install

   # For Pusher Cloud, configure in .env
   BROADCAST_DRIVER=pusher
   ```

7. **Storage Setup**
   ```bash
   php artisan storage:link
   ```

8. **Start the Application**
   ```bash
   # Start Laravel server
   php artisan serve

   # Start WebSocket server (in another terminal)
   php artisan reverb:start

   # Start queue worker (in another terminal)
   php artisan queue:work
   ```

### Production Deployment

1. **Web Server Configuration**
   - Nginx or Apache with PHP-FPM
   - SSL certificate configuration
   - Domain configuration

2. **Process Management**
   - Supervisor for queue workers
   - Systemd for WebSocket server
   - Load balancer configuration

3. **Monitoring**
   - Laravel Telescope for debugging
   - Server monitoring tools
   - Log aggregation

## 📊 Admin Dashboard

### Features
- **User Management**: View, block, manage users
- **Content Moderation**: Monitor and moderate messages
- **System Monitoring**: Health checks, performance metrics
- **Broadcasting Configuration**: Switch between Pusher/Reverb
- **Analytics**: Message volume, user statistics
- **Settings Management**: System configuration

### Access
Navigate to `/admin` in your browser and login with admin credentials.

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **Unit Tests**: Core functionality
- **Feature Tests**: API endpoints
- **Integration Tests**: End-to-end workflows
- **Performance Tests**: Load testing

## 📚 Documentation

- **[API Documentation](./docs/api-documentation/index.html)**: Complete API reference with examples
- **[Product Requirements Document](./COMPREHENSIVE_PRD_CHATAPP.md)**: Detailed system specifications
- **[Broadcast Configuration](./docs/broadcast-settings-complete-migration.md)**: Broadcasting setup guide
- **[Mobile Implementation](./docs/mobile-app-broadcast-integration.md)**: Mobile app integration guide

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_NAME=ChatApp
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatapp
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=chatapp
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- React Native Community
- All contributors and testers

---

**Built with ❤️ for seamless communication**
>>>>>>> master
