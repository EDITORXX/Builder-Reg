<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitSchedule extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'builder_firm_id',
        'project_id',
        'channel_partner_id',
        'customer_name',
        'customer_mobile',
        'customer_email',
        'scheduled_at',
        'token',
        'status',
        'lead_id',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function builderFirm(): BelongsTo
    {
        return $this->belongsTo(BuilderFirm::class, 'builder_firm_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function channelPartner(): BelongsTo
    {
        return $this->belongsTo(ChannelPartner::class, 'channel_partner_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isTokenExpired(): bool
    {
        $graceHours = (int) config('visit_schedule.token_grace_hours', 24);
        return $this->scheduled_at->addHours($graceHours)->isPast();
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeActiveCount($query, int $builderFirmId)
    {
        return $query->where('builder_firm_id', $builderFirmId)
            ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_CHECKED_IN]);
    }
}
