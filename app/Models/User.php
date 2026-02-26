<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_BUILDER_ADMIN = 'builder_admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_SALES_EXEC = 'sales_exec';
    public const ROLE_CHANNEL_PARTNER = 'channel_partner';
    public const ROLE_VIEWER = 'viewer';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role',
        'builder_firm_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function builderFirm(): BelongsTo
    {
        return $this->belongsTo(BuilderFirm::class, 'builder_firm_id');
    }

    public function channelPartner(): HasOne
    {
        return $this->hasOne(ChannelPartner::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isBuilderAdmin(): bool
    {
        return $this->role === self::ROLE_BUILDER_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isSalesExec(): bool
    {
        return $this->role === self::ROLE_SALES_EXEC;
    }

    public function isChannelPartner(): bool
    {
        return $this->role === self::ROLE_CHANNEL_PARTNER;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function belongsToBuilderFirm(?int $builderFirmId): bool
    {
        if ($builderFirmId === null) {
            return false;
        }
        return (int) $this->builder_firm_id === (int) $builderFirmId;
    }

    /** Full URL for profile picture, or null if not set. */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }
        $path = ltrim($this->avatar, '/');
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        return asset('storage/' . $path);
    }

    /** Human-readable role label for UI (e.g. SaaS Admin, Builder Admin, Channel Partner). */
    public function getRoleLabel(): string
    {
        return match ($this->role ?? '') {
            self::ROLE_SUPER_ADMIN => 'SaaS Admin',
            self::ROLE_BUILDER_ADMIN => 'Builder Admin',
            self::ROLE_MANAGER => 'Builder Manager',
            self::ROLE_SALES_EXEC => 'Sales Exec',
            self::ROLE_VIEWER => 'Viewer',
            self::ROLE_CHANNEL_PARTNER => 'Channel Partner',
            default => str_replace('_', ' ', ucfirst($this->role ?? 'User')),
        };
    }
}
