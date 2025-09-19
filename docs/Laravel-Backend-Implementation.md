# WhatsApp Clone - Laravel Backend Implementation Guide

## Project Setup & Installation

### 1. Create Laravel Project
```bash
composer create-project laravel/laravel whatsapp-clone-api
cd whatsapp-clone-api
```

### 2. Install Required Packages
```bash
# Core packages
composer require laravel/sanctum
composer require pusher/pusher-php-server
composer require intervention/image
composer require spatie/laravel-permission
composer require laravel/horizon
composer require predis/predis
composer require league/flysystem-aws-s3-v3
composer require twilio/sdk
composer require spatie/laravel-medialibrary

# Development packages
composer require --dev laravel/telescope
composer require --dev barryvdh/laravel-ide-helper
```

### 3. Project Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── ChatController.php
│   │   │   ├── MessageController.php
│   │   │   ├── GroupController.php
│   │   │   ├── StatusController.php
│   │   │   ├── ContactController.php
│   │   │   └── CallController.php
│   │   └── Controller.php
│   ├── Middleware/
│   │   ├── AuthenticateApi.php
│   │   └── TrackUserActivity.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Chat/
│   │   ├── Group/
│   │   └── User/
│   └── Resources/
│       ├── UserResource.php
│       ├── ChatResource.php
│       ├── MessageResource.php
│       └── GroupResource.php
├── Models/
│   ├── User.php
│   ├── Chat.php
│   ├── Message.php
│   ├── ChatParticipant.php
│   ├── MessageReaction.php
│   ├── Status.php
│   ├── StatusView.php
│   ├── Contact.php
│   └── Call.php
├── Services/
│   ├── AuthService.php
│   ├── ChatService.php
│   ├── MessageService.php
│   ├── MediaService.php
│   ├── NotificationService.php
│   └── TwilioService.php
├── Events/
│   ├── MessageSent.php
│   ├── MessageRead.php
│   ├── UserOnline.php
│   ├── UserTyping.php
│   └── CallInitiated.php
├── Listeners/
│   ├── SendMessageNotification.php
│   ├── UpdateMessageStatus.php
│   └── BroadcastUserActivity.php
├── Jobs/
│   ├── ProcessMediaUpload.php
│   ├── SendSmsOtp.php
│   └── CleanupExpiredStatus.php
└── Broadcasting/
    ├── ChatChannel.php
    └── UserChannel.php
```

## Configuration Files

### 1. Environment Configuration (.env)
```bash
APP_NAME="WhatsApp Clone API"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_clone
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Reverb Configuration
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=whatsapp-clone-media
AWS_USE_PATH_STYLE_ENDPOINT=false

# Twilio Configuration
TWILIO_SID=your-twilio-sid
TWILIO_TOKEN=your-twilio-token
TWILIO_FROM=your-twilio-phone

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

### 2. Database Configuration (config/database.php)
```php
<?php
// config/database.php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
];
```

## Database Migrations

### 1. Users Migration
```php
<?php
// database/migrations/2024_01_01_000001_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->unique();
            $table->string('country_code', 5);
            $table->string('name');
            $table->string('avatar_url', 500)->nullable();
            $table->text('about')->default('Hey there! I am using WhatsApp Clone.');
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_online')->default(false);
            $table->enum('privacy_last_seen', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('privacy_profile_photo', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->enum('privacy_about', ['everyone', 'contacts', 'nobody'])->default('everyone');
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['phone_number']);
            $table->index(['last_seen_at']);
            $table->index(['is_online']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 2. Chats Migration
```php
<?php
// database/migrations/2024_01_01_000002_create_chats_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['private', 'group']);
            $table->string('name')->nullable(); // For group chats
            $table->text('description')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type']);
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
```

### 3. Chat Participants Migration
```php
<?php
// database/migrations/2024_01_01_000003_create_chat_participants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['member', 'admin'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('muted_until')->nullable();
            $table->foreignId('last_read_message_id')->nullable()->constrained('messages')->onDelete('set null');
            
            $table->unique(['chat_id', 'user_id']);
            $table->index(['chat_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};
```

### 4. Messages Migration
```php
<?php
// database/migrations/2024_01_01_000004_create_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->enum('message_type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact']);
            $table->text('content')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->bigInteger('media_size')->nullable();
            $table->integer('media_duration')->nullable(); // seconds
            $table->string('media_mime_type', 100)->nullable();
            $table->string('file_name')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->json('contact_data')->nullable();
            $table->enum('status', ['sending', 'sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['chat_id']);
            $table->index(['sender_id']);
            $table->index(['sent_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

### 5. Additional Migrations
```php
<?php
// database/migrations/2024_01_01_000005_create_message_reactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('emoji', 10);
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['message_id', 'user_id', 'emoji']);
            $table->index(['message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
    }
};
```

## Models

### 1. User Model
```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'phone_number',
        'country_code',
        'name',
        'avatar_url',
        'about',
        'last_seen_at',
        'is_online',
        'privacy_last_seen',
        'privacy_profile_photo',
        'privacy_about',
        'phone_verified_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    // Relationships
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants')
                    ->withPivot(['role', 'joined_at', 'left_at', 'muted_until', 'last_read_message_id'])
                    ->withTimestamps();
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_user_id')
                    ->withPivot(['contact_name', 'is_blocked', 'added_at']);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    // Scopes
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    // Methods
    public function isOnline(): bool
    {
        return $this->is_online;
    }

    public function markAsOnline(): void
    {
        $this->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
    }

    public function markAsOffline(): void
    {
        $this->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);
    }

    public function getPrivateChat(User $user): ?Chat
    {
        return $this->chats()
                    ->where('type', 'private')
                    ->whereHas('participants', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->first();
    }

    public function canSeeLastSeen(User $user): bool
    {
        switch ($this->privacy_last_seen) {
            case 'everyone':
                return true;
            case 'contacts':
                return $this->contacts()->where('contact_user_id', $user->id)->exists();
            case 'nobody':
                return false;
            default:
                return false;
        }
    }
}
```

### 2. Chat Model
```php
<?php
// app/Models/Chat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'description',
        'avatar_url',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
                    ->withPivot(['role', 'joined_at', 'left_at', 'muted_until', 'last_read_message_id'])
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    // Scopes
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    // Methods
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    public function addParticipant(User $user, string $role = 'member'): void
    {
        $this->participants()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeParticipant(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'left_at' => now(),
        ]);
    }

    public function getLastMessage(): ?Message
    {
        return $this->messages()->latest('sent_at')->first();
    }

    public function getUnreadCount(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        
        if (!$participant || !$participant->pivot->last_read_message_id) {
            return $this->messages()->count();
        }

        return $this->messages()
                    ->where('id', '>', $participant->pivot->last_read_message_id)
                    ->where('sender_id', '!=', $user->id)
                    ->count();
    }

    public function markAsRead(User $user, int $messageId): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_message_id' => $messageId,
        ]);
    }
}
```

### 3. Message Model
```php
<?php
// app/Models/Message.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'reply_to_message_id',
        'message_type',
        'content',
        'media_url',
        'media_size',
        'media_duration',
        'media_mime_type',
        'file_name',
        'thumbnail_url',
        'latitude',
        'longitude',
        'location_name',
        'contact_data',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'edited_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'contact_data' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeMedia($query)
    {
        return $query->whereIn('message_type', ['image', 'video', 'audio', 'document']);
    }

    public function scopeUnread($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    // Methods
    public function isMediaMessage(): bool
    {
        return in_array($this->message_type, ['image', 'video', 'audio', 'document']);
    }

    public function markAsDelivered(): void
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }
    }

    public function markAsRead(): void
    {
        if (in_array($this->status, ['sent', 'delivered'])) {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    public function edit(string $content): void
    {
        $this->update([
            'content' => $content,
            'edited_at' => now(),
        ]);
    }

    public function addReaction(User $user, string $emoji): void
    {
        $this->reactions()->updateOrCreate(
            ['user_id' => $user->id, 'emoji' => $emoji],
            ['created_at' => now()]
        );
    }

    public function removeReaction(User $user, string $emoji): void
    {
        $this->reactions()
             ->where('user_id', $user->id)
             ->where('emoji', $emoji)
             ->delete();
    }
}
```

## Controllers

### 1. Auth Controller
```php
<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private TwilioService $twilioService
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $phoneNumber = $request->phone_number;
        $countryCode = $request->country_code;
        
        // Generate OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in cache for 5 minutes
        $cacheKey = "otp:{$phoneNumber}";
        Cache::put($cacheKey, $otp, now()->addMinutes(5));
        
        // Send OTP via Twilio
        $this->twilioService->sendSms($phoneNumber, "Your WhatsApp Clone verification code is: {$otp}");
        
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'expires_at' => now()->addMinutes(5)->toISOString(),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $phoneNumber = $request->phone_number;
        $otp = $request->otp;
        
        // Verify OTP
        $cacheKey = "otp:{$phoneNumber}";
        $storedOtp = Cache::get($cacheKey);
        
        if (!$storedOtp || $storedOtp != $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 400);
        }
        
        // Clear OTP from cache
        Cache::forget($cacheKey);
        
        // Find or create user
        $user = User::where('phone_number', $phoneNumber)->first();
        
        if (!$user) {
            $user = User::create([
                'phone_number' => $phoneNumber,
                'country_code' => $request->country_code,
                'name' => $request->name ?? 'User',
                'phone_verified_at' => now(),
            ]);
        } else {
            $user->update(['phone_verified_at' => now()]);
        }
        
        // Generate tokens
        $tokens = $this->authService->generateTokens($user);
        
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'tokens' => $tokens,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $this->authService->generateTokens($user);
        
        return response()->json([
            'access_token' => $tokens['access_token'],
            'expires_at' => $tokens['expires_at'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
```

### 2. Chat Controller
```php
<?php
// app/Http/Controllers/Api/ChatController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        
        $chats = $user->chats()
                     ->with(['participants', 'messages' => function ($query) {
                         $query->latest('sent_at')->limit(1);
                     }])
                     ->orderBy('updated_at', 'desc')
                     ->paginate($limit, ['*'], 'page', $page);
        
        return response()->json([
            'chats' => ChatResource::collection($chats->items()),
            'pagination' => [
                'current_page' => $chats->currentPage(),
                'total_pages' => $chats->lastPage(),
                'total_items' => $chats->total(),
            ],
        ]);
    }

    public function show(Chat $chat, Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is participant
        if (!$chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found',
            ], 404);
        }
        
        return response()->json(new ChatResource($chat));
    }

    public function getOrCreatePrivateChat(Request $request): JsonResponse
    {
        $user = $request->user();
        $contactId = $request->contact_id;
        
        $contact = User::findOrFail($contactId);
        
        // Check if private chat already exists
        $chat = $user->getPrivateChat($contact);
        
        if (!$chat) {
            $chat = $this->chatService->createPrivateChat($user, $contact);
        }
        
        return response()->json(new ChatResource($chat));
    }

    public function markAsRead(Chat $chat, Request $request): JsonResponse
    {
        $user = $request->user();
        $messageId = $request->message_id;
        
        $chat->markAsRead($user, $messageId);
        
        return response()->json([
            'success' => true,
            'read_at' => now()->toISOString(),
        ]);
    }
}
```

### 3. Message Controller
```php
<?php
// app/Http/Controllers/Api/MessageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Services\MessageService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private MediaService $mediaService
    ) {}

    public function index(Chat $chat, Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);
        $beforeMessageId = $request->get('before_message_id');
        
        // Check if user is participant
        if (!$chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found',
            ], 404);
        }
        
        $query = $chat->messages()
                     ->with(['sender', 'reactions.user', 'replyTo'])
                     ->orderBy('sent_at', 'desc');
        
        if ($beforeMessageId) {
            $query->where('id', '<', $beforeMessageId);
        }
        
        $messages = $query->paginate($limit, ['*'], 'page', $page);
        
        return response()->json([
            'messages' => MessageResource::collection($messages->items()->reverse()),
            'pagination' => [
                'has_more' => $messages->hasMorePages(),
                'next_before_message_id' => $messages->items()->first()?->id,
            ],
        ]);
    }

    public function store(Chat $chat, SendMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is participant
        if (!$chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Chat not found',
            ], 404);
        }
        
        $messageData = $request->validated();
        $messageData['chat_id'] = $chat->id;
        $messageData['sender_id'] = $user->id;
        
        // Handle file upload for media messages
        if ($request->hasFile('file')) {
            $mediaData = $this->mediaService->uploadMedia($request->file('file'), $messageData['message_type']);
            $messageData = array_merge($messageData, $mediaData);
        }
        
        $message = $this->messageService->createMessage($messageData);
        
        return response()->json([
            'message' => new MessageResource($message),
        ], 201);
    }

    public function destroy(Message $message, Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is the sender
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $message->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully',
        ]);
    }

    public function addReaction(Message $message, Request $request): JsonResponse
    {
        $user = $request->user();
        $emoji = $request->emoji;
        
        $message->addReaction($user, $emoji);
        
        $reactionCount = $message->reactions()->where('emoji', $emoji)->count();
        
        return response()->json([
            'success' => true,
            'reaction' => [
                'message_id' => $message->id,
                'emoji' => $emoji,
                'count' => $reactionCount,
            ],
        ]);
    }
}
```

## Services

### 1. Auth Service
```php
<?php
// app/Services/AuthService.php

namespace App\Services;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function generateTokens(User $user): array
    {
        // Delete old tokens
        $user->tokens()->delete();
        
        // Create access token
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());
        
        // Create refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(14));
        
        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expires_at' => $accessToken->accessToken->expires_at->toISOString(),
        ];
    }

    public function refreshToken(string $refreshToken): ?array
    {
        $token = PersonalAccessToken::findToken($refreshToken);
        
        if (!$token || !$token->can('refresh') || $token->expires_at < now()) {
            return null;
        }
        
        $user = $token->tokenable;
        
        // Delete old tokens
        $user->tokens()->delete();
        
        return $this->generateTokens($user);
    }
}
```

### 2. Chat Service
```php
<?php
// app/Services/ChatService.php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\User;

class ChatService
{
    public function createPrivateChat(User $user1, User $user2): Chat
    {
        $chat = Chat::create([
            'type' => 'private',
            'created_by' => $user1->id,
        ]);
        
        $chat->addParticipant($user1);
        $chat->addParticipant($user2);
        
        return $chat;
    }

    public function createGroupChat(User $creator, array $participants, string $name, ?string $description = null): Chat
    {
        $chat = Chat::create([
            'type' => 'group',
            'name' => $name,
            'description' => $description,
            'created_by' => $creator->id,
        ]);
        
        $chat->addParticipant($creator, 'admin');
        
        foreach ($participants as $userId) {
            $chat->addParticipant(User::find($userId));
        }
        
        return $chat;
    }
}
```

## API Routes

### routes/api.php
```php
<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CallController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
    
    // User routes
    Route::prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('avatar', [UserController::class, 'uploadAvatar']);
        Route::put('privacy', [UserController::class, 'updatePrivacy']);
    });
    
    // Chat routes
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('private', [ChatController::class, 'getOrCreatePrivateChat']);
        Route::get('{chat}', [ChatController::class, 'show']);
        Route::put('{chat}/read', [ChatController::class, 'markAsRead']);
        
        // Message routes
        Route::get('{chat}/messages', [MessageController::class, 'index']);
        Route::post('{chat}/messages', [MessageController::class, 'store']);
    });
    
    // Message routes
    Route::prefix('messages')->group(function () {
        Route::delete('{message}', [MessageController::class, 'destroy']);
        Route::post('{message}/reactions', [MessageController::class, 'addReaction']);
        Route::delete('{message}/reactions', [MessageController::class, 'removeReaction']);
    });
    
    // Group routes
    Route::prefix('groups')->group(function () {
        Route::post('/', [GroupController::class, 'store']);
        Route::get('{group}', [GroupController::class, 'show']);
        Route::put('{group}', [GroupController::class, 'update']);
        Route::post('{group}/leave', [GroupController::class, 'leave']);
        Route::post('{group}/members', [GroupController::class, 'addMembers']);
        Route::delete('{group}/members/{user}', [GroupController::class, 'removeMember']);
    });
    
    // Status routes
    Route::prefix('status')->group(function () {
        Route::get('/', [StatusController::class, 'index']);
        Route::post('/', [StatusController::class, 'store']);
        Route::post('{status}/view', [StatusController::class, 'view']);
        Route::get('{status}/views', [StatusController::class, 'getViews']);
        Route::delete('{status}', [StatusController::class, 'destroy']);
    });
    
    // Contact routes
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/', [ContactController::class, 'store']);
        Route::put('{contact}/block', [ContactController::class, 'toggleBlock']);
        Route::delete('{contact}', [ContactController::class, 'destroy']);
    });
    
    // Call routes
    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::post('/', [CallController::class, 'initiate']);
        Route::put('{call}/answer', [CallController::class, 'answer']);
        Route::put('{call}/end', [CallController::class, 'end']);
        Route::put('{call}/decline', [CallController::class, 'decline']);
    });
});
```

This comprehensive implementation provides a solid foundation for your WhatsApp clone Laravel backend with all the necessary features, proper architecture, and scalable design patterns. 