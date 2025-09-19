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
        'viewer_ip',
        'viewer_device'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
