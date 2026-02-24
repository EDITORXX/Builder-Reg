<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'plan_id',
        'scheduled_visit_enabled',
        'scheduled_visit_limit',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'scheduled_visit_enabled' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'builder_firm_id');
    }

    public function adminUser(): ?User
    {
        return $this->users()->where('role', User::ROLE_BUILDER_ADMIN)->first();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'builder_firm_id');
    }

    public function cpApplications(): HasMany
    {
        return $this->hasMany(CpApplication::class, 'builder_firm_id');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'builder_firm_id');
    }

    public function visitSchedules(): HasMany
    {
        return $this->hasMany(VisitSchedule::class, 'builder_firm_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function getMaxUsers(): int
    {
        return $this->plan?->max_users ?? 2;
    }

    public function getMaxProjects(): int
    {
        return $this->plan?->max_projects ?? 1;
    }

    public function getMaxChannelPartners(): int
    {
        return $this->plan?->max_channel_partners ?? 10;
    }

    public function getMaxLeads(): int
    {
        return $this->plan?->max_leads ?? 200;
    }

    public function getLockDaysForProject(?int $projectOverride): int
    {
        return $projectOverride ?? $this->default_lock_days;
    }

    public function getLogoUrl(): ?string
    {
        return $this->settings['logo_url'] ?? null;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->settings['primary_color'] ?? null;
    }

    public function getRegistrationPageBg(): string
    {
        $v = $this->settings['registration_bg'] ?? null;
        return $v !== null && $v !== '' ? (string) $v : 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%)';
    }

    public function getRegistrationCardBg(): string
    {
        $v = $this->settings['registration_card_bg'] ?? null;
        return $v !== null && $v !== '' ? (string) $v : '#ffffff';
    }

    public function getRegistrationTitleColor(): string
    {
        $v = $this->settings['registration_title_color'] ?? null;
        return $v !== null && $v !== '' ? (string) $v : '#1e3d3d';
    }

    public function getRegistrationTextColor(): string
    {
        $v = $this->settings['registration_text_color'] ?? null;
        return $v !== null && $v !== '' ? (string) $v : '#1e3d3d';
    }

    public function getRegistrationSubtitleColor(): string
    {
        $v = $this->settings['registration_subtitle_color'] ?? null;
        return $v !== null && $v !== '' ? (string) $v : '#4a6b6b';
    }

    public function getMailFromAddress(): ?string
    {
        $addr = $this->settings['mail_from_address'] ?? null;
        return $addr !== null && $addr !== '' ? (string) $addr : null;
    }

    public function getMailFromName(): string
    {
        $name = $this->settings['mail_from_name'] ?? null;
        return $name !== null && $name !== '' ? (string) $name : ($this->name ?? config('mail.from.name', 'Builder'));
    }
}
