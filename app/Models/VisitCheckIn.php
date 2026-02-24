<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitCheckIn extends Model
{
    public const VERIFICATION_PENDING = 'pending_verification';
    public const VERIFICATION_VERIFIED = 'verified_visit';
    public const VERIFICATION_REJECTED = 'rejected_verification';

    public const TYPE_SCHEDULED_CHECKIN = 'scheduled_checkin';
    public const TYPE_DIRECT = 'direct';

    protected $fillable = [
        'lead_id',
        'visit_schedule_id',
        'project_id',
        'channel_partner_id',
        'customer_mobile',
        'submitted_at',
        'visit_photo_path',
        'verification_status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'visit_type',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function visitSchedule(): BelongsTo
    {
        return $this->belongsTo(VisitSchedule::class, 'visit_schedule_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function channelPartner(): BelongsTo
    {
        return $this->belongsTo(ChannelPartner::class, 'channel_partner_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->verification_status === self::VERIFICATION_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED;
    }
}
