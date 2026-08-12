<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'identifier',
        'channel',
        'purpose',
        'code_hash',
        'attempts',
        'expires_at',
        'consumed_at',
        'verification_token',
        'verified_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /** Codes that are still usable: not spent, not expired. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->whereNull('consumed_at')->where('expires_at', '>', now());
    }

    public function scopeFor(Builder $query, string $identifier, string $purpose): Builder
    {
        return $query->where('identifier', $identifier)->where('purpose', $purpose);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /** Spend the code so it can never be replayed. */
    public function consume(): void
    {
        $this->forceFill(['consumed_at' => now()])->save();
    }
}
