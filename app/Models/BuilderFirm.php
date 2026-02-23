<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuilderFirm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'default_lock_days',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'builder_firm_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'builder_firm_id');
    }

    public function cpApplications(): HasMany
    {
        return $this->hasMany(CpApplication::class, 'builder_firm_id');
    }

    public function getLockDaysForProject(?int $projectOverride): int
    {
        return $projectOverride ?? $this->default_lock_days;
    }
}
