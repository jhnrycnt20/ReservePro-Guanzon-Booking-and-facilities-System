<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'discount_percent',
        'applies_to_all',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
        'created_by',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'applies_to_all' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function appliesToAccommodation(int $accommodationId): bool
    {
        if ($this->applies_to_all) {
            return true;
        }

        return $this->accommodations()->where('accommodations.id', $accommodationId)->exists();
    }

    public function discountedRate(float $rate): float
    {
        $percent = max(0, min(100, (float) $this->discount_percent));

        return round($rate * (1 - ($percent / 100)), 2);
    }

    public function discountAmount(float $amount): float
    {
        $percent = max(0, min(100, (float) $this->discount_percent));

        return round($amount * ($percent / 100), 2);
    }
}
