<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getLabelAttribute(): string
    {
        return match ($this->action) {
            'payment.recorded' => 'Payment received',
            'payment.verified' => 'Payment verified',
            'payment.rejected' => 'Payment rejected',
            'booking.created' => 'New reservation',
            'booking.checked_in' => 'Guest checked in',
            'booking.checked_out' => 'Guest checked out',
            'booking.approved' => 'Reservation approved',
            'booking.rejected' => 'Reservation rejected',
            'booking.cancelled' => 'Reservation cancelled',
            default => str_replace(['.', '_'], ' ', ucwords((string) $this->action, '._')),
        };
    }
}
