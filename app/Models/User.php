<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'country_code',
        'password',
        'avatar_url',
        'about',
        'last_seen_at',
        'is_online',
        'privacy_last_seen',
        'privacy_profile_photo',
        'privacy_about',
        'read_receipts',
        'email_verified_at',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'is_online' => 'boolean',
            'read_receipts' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relationships

    /**
     * Get the chats that the user created
     */
    public function createdChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'created_by');
    }

    /**
     * Get the chats that the user participates in
     */
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants')
            ->withPivot(['role', 'joined_at', 'left_at', 'muted_until', 'last_read_message_id', 'is_archived', 'is_pinned']);
    }

    /**
     * Get the messages sent by the user
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the user's contacts
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'user_id');
    }

    /**
     * Get the users who have this user as a contact
     */
    public function contactedBy(): HasMany
    {
        return $this->hasMany(Contact::class, 'contact_user_id');
    }

    /**
     * Get the statuses posted by the user
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    /**
     * Get the status views by the user
     */
    public function statusViews(): HasMany
    {
        return $this->hasMany(StatusView::class, 'viewer_id');
    }

    /**
     * Get the calls initiated by the user
     */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    /**
     * Get the calls initiated by the user (alias for calls)
     */
    public function sentCalls(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    /**
     * Get calls where user is a participant (received calls)
     * Returns a builder that can be used for counting
     */
    public function receivedCalls()
    {
        $chatIds = $this->chats()->select('chats.id')->pluck('chats.id');
        return Call::whereIn('chat_id', $chatIds)->where('caller_id', '!=', $this->id);
    }

    /**
     * Get the message reactions by the user
     */
    public function messageReactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    // Helper methods

    /**
     * Check if user is online
     */
    public function isOnline(): bool
    {
        return $this->is_online;
    }

    /**
     * Update online status
     */
    public function updateOnlineStatus(bool $status): void
    {
        $this->update([
            'is_online' => $status,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Get blocked contacts
     */
    public function blockedContacts()
    {
        return $this->contacts()->where('is_blocked', true);
    }

    /**
     * Check if user has blocked another user
     */
    public function hasBlocked(User $user): bool
    {
        return $this->blockedContacts()->where('contact_user_id', $user->id)->exists();
    }

    /**
     * Check if user is blocked by another user
     */
    public function isBlockedBy(User $user): bool
    {
        return $user->hasBlocked($this);
    }
}
