<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    public const TYPE_CP_REGISTRATION = 'cp_registration';
    public const TYPE_CUSTOMER_REGISTRATION = 'customer_registration';

    protected $fillable = [
        'builder_firm_id',
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function builderFirm(): BelongsTo
    {
        return $this->belongsTo(BuilderFirm::class, 'builder_firm_id');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActiveForBuilder(BuilderFirm $builder, string $type): ?self
    {
        return static::where('builder_firm_id', $builder->id)
            ->where('type', $type)
            ->where('is_active', true)
            ->with('formFields')
            ->first();
    }
}
