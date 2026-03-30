<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_user_id',
        'contact_name',
        'is_blocked',
        'is_favorite',
        'added_at',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'is_favorite' => 'boolean',
        'added_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the user who owns this contact
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who is the contact
     */
    public function contactUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }

    // Scopes

    /**
     * Scope to get only blocked contacts
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope to get only unblocked contacts
     */
    public function scopeUnblocked(Builder $query): Builder
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope to get only favorite contacts
     */
    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('is_favorite', true);
    }

    // Helper methods

    /**
     * Block this contact
     */
    public function block(): void
    {
        $this->update(['is_blocked' => true]);
    }

    /**
     * Unblock this contact
     */
    public function unblock(): void
    {
        $this->update(['is_blocked' => false]);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(): void
    {
        $this->update(['is_favorite' => !$this->is_favorite]);
    }

    /**
     * Sync contacts from phone numbers
     */
    public static function syncFromPhoneNumbers(User $user, array $phoneNumbers): array
    {
        $synced = [];
        $existingUsers = User::whereIn('phone_number', $phoneNumbers)->get();

        foreach ($existingUsers as $existingUser) {
            if ($existingUser->id === $user->id) {
                continue; // Skip self
            }

            $contact = static::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'contact_user_id' => $existingUser->id,
                ],
                [
                    'contact_name' => $existingUser->name,
                    'added_at' => now(),
                ]
            );

            $synced[] = $contact;
        }

        return $synced;
    }

    /**
     * Get mutual contacts between two users
     */
    public static function getMutualContacts(User $user1, User $user2): array
    {
        $user1Contacts = static::where('user_id', $user1->id)
            ->where('is_blocked', false)
            ->pluck('contact_user_id');

        $user2Contacts = static::where('user_id', $user2->id)
            ->where('is_blocked', false)
            ->pluck('contact_user_id');

        $mutualContactIds = $user1Contacts->intersect($user2Contacts);

        return User::whereIn('id', $mutualContactIds)->get()->toArray();
    }
}
