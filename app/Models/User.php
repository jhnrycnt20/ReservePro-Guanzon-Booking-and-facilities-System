<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Helpers\RoleRedirect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function guest(): HasOne
    {
        return $this->hasOne(Guest::class);
    }

    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string|UserRole $role): bool
    {
        $slug = $role instanceof UserRole ? $role->value : $role;

        return $this->role?->slug === $slug;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isFrontDesk(): bool
    {
        return $this->hasRole(UserRole::FrontDesk);
    }

    public function isSecurity(): bool
    {
        return $this->hasRole(UserRole::Security);
    }

    public function isGuestRole(): bool
    {
        return $this->hasRole(UserRole::Guest);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function dashboardRoute(): string
    {
        return RoleRedirect::dashboardRoute($this);
    }
}
