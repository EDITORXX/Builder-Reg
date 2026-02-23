<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'builder_firm_id',
        'name',
        'location',
        'lock_days_override',
        'status',
    ];

    public function builderFirm(): BelongsTo
    {
        return $this->belongsTo(BuilderFirm::class, 'builder_firm_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function leadLocks(): HasMany
    {
        return $this->hasMany(LeadLock::class);
    }

    public function getLockDays(): int
    {
        return $this->lock_days_override ?? $this->builderFirm->default_lock_days;
    }
}
