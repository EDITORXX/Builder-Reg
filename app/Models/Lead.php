<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    // Legacy single status (deprecated; use three-track below)
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_VISIT_SCHEDULED = 'visit_scheduled';
    public const STATUS_VISIT_DONE = 'visit_done';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_LOST = 'lost';
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_VERIFIED_VISIT = 'verified_visit';
    public const STATUS_REJECTED = 'rejected';

    // Visit status (lead.visit_status)
    public const VISIT_SCHEDULED = 'visit_scheduled';
    public const VISITED = 'visited';
    public const REVISIT = 'revisit';
    public const VISIT_CANCELLED = 'visit_cancelled';

    // Verification status (lead.verification_status)
    public const PENDING_VERIFICATION = 'pending_verification';
    public const VERIFIED_VISIT = 'verified_visit';
    public const REJECTED_VERIFICATION = 'rejected_verification';

    // Sales status (lead.sales_status)
    public const SALES_NEW = 'new';
    public const SALES_NEGOTIATION = 'negotiation';
    public const SALES_HOLD = 'hold';
    public const SALES_BOOKED = 'booked';
    public const SALES_LOST = 'lost';

    public const SOURCE_CHANNEL_PARTNER = 'channel_partner';
    public const SOURCE_DIRECT = 'direct';
    public const SOURCE_ONLINE = 'online';
    public const SOURCE_REFERRAL = 'referral';
    public const SOURCE_REVISIT = 'revisit';

    protected $fillable = [
        'project_id',
        'customer_id',
        'channel_partner_id',
        'assigned_to',
        'created_by',
        'status',
        'source',
        'budget',
        'property_type',
        'notes',
        'visit_photo_path',
        'verification_reject_reason',
        'visit_status',
        'verification_status',
        'sales_status',
        'last_verified_visit_at',
    ];

    protected function casts(): array
    {
        return [
            'last_verified_visit_at' => 'datetime',
        ];
    }

    public function visitCheckIns(): HasMany
    {
        return $this->hasMany(VisitCheckIn::class, 'lead_id');
    }

    public function isPendingVerification(): bool
    {
        return $this->verification_status === self::PENDING_VERIFICATION;
    }

    public function isVerifiedVisit(): bool
    {
        return $this->verification_status === self::VERIFIED_VISIT;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function channelPartner(): BelongsTo
    {
        return $this->belongsTo(ChannelPartner::class, 'channel_partner_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function leadActivities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function leadLocks(): HasMany
    {
        return $this->hasMany(LeadLock::class);
    }

    public function activeLock()
    {
        return $this->hasOne(LeadLock::class)->where('status', LeadLock::STATUS_ACTIVE);
    }

    public function isBooked(): bool
    {
        return $this->sales_status === self::SALES_BOOKED;
    }
}
