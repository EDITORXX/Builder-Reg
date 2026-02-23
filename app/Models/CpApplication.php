<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpApplication extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_INFO = 'needs_info';

    protected $fillable = [
        'channel_partner_id',
        'builder_firm_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function channelPartner(): BelongsTo
    {
        return $this->belongsTo(ChannelPartner::class, 'channel_partner_id');
    }

    public function builderFirm(): BelongsTo
    {
        return $this->belongsTo(BuilderFirm::class, 'builder_firm_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
