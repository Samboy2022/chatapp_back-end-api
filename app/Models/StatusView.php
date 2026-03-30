<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusView extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'viewer_id',
        'viewed_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the status that was viewed
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the user who viewed the status
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
