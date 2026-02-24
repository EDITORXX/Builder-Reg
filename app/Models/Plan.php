<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'max_users',
        'max_projects',
        'max_channel_partners',
        'max_leads',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function builderFirms(): HasMany
    {
        return $this->hasMany(BuilderFirm::class, 'plan_id');
    }
}
