<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitOtp extends Model
{
    use HasFactory;

    public const MAX_ATTEMPTS = 5;
    public const VALIDITY_MINUTES = 10;

    protected $fillable = [
        'visit_id',
        'otp_hash',
        'expires_at',
        'verified_at',
        'attempt_count',
    ];

    protected $hidden = [
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isMaxAttemptsReached(): bool
    {
        return $this->attempt_count >= self::MAX_ATTEMPTS;
    }
}
