<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_MISSED = 'missed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESCHEDULED = 'rescheduled';

    public const CONFIRM_METHOD_OTP = 'otp';
    public const CONFIRM_METHOD_MANUAL = 'manual';
    public const CONFIRM_METHOD_QR = 'qr';

    protected $fillable = [
        'lead_id',
        'scheduled_at',
        'confirmed_at',
        'status',
        'confirm_method',
        'confirmed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function visitOtp(): HasOne
    {
        return $this->hasOne(VisitOtp::class);
    }
}
