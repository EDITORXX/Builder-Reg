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

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_VISIT_SCHEDULED = 'visit_scheduled';
    public const STATUS_VISIT_DONE = 'visit_done';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_LOST = 'lost';

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
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
        ];
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
        return $this->status === self::STATUS_BOOKED;
    }
}
