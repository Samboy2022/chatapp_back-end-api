<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'user_id', 
        'joined_at',
        'left_at',
        'status'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the call this participant belongs to
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Get the user who is this participant
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
