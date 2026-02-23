<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firm_name',
        'rera_number',
        'pan_number',
        'gst_number',
        'documents',
    ];

    protected function casts(): array
    {
        return [
            'documents' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cpApplications(): HasMany
    {
        return $this->hasMany(CpApplication::class, 'channel_partner_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'channel_partner_id');
    }

    public function leadLocks(): HasMany
    {
        return $this->hasMany(LeadLock::class, 'channel_partner_id');
    }

    public function isApprovedForBuilder(int $builderFirmId): bool
    {
        return $this->cpApplications()
            ->where('builder_firm_id', $builderFirmId)
            ->where('status', 'approved')
            ->exists();
    }
}
