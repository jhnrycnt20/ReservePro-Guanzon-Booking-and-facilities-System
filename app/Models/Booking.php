<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'guest_id',
        'accommodation_id',
        'guest_name',
        'contact_number',
        'email',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'number_of_guests',
        'special_requests',
        'status',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'is_walk_in',
        'created_by',
        'promo_id',
        'promo_code',
        'original_amount',
        'discount_amount',
        'discount_percent',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'number_of_guests' => 'integer',
        'status' => BookingStatus::class,
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_walk_in' => 'boolean',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checkIn(): HasOne
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut(): HasOne
    {
        return $this->hasOne(CheckOut::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class);
    }

    /**
     * Short display code for lists (e.g. BK-7K2M or BK-88166D from older long IDs).
     */
    public function depositRequiredAmount(): float
    {
        return round(((float) $this->total_amount) * 0.5, 2);
    }

    public function hasMetDepositRequirement(): bool
    {
        return ((float) $this->paid_amount) + 0.009 >= $this->depositRequiredAmount();
    }

    public function scopeAwaitingDeposit(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->whereRaw('(paid_amount + 0.009) < (total_amount * 0.5)');
    }

    /** Reserved or Booked — not fully paid, not yet checked in. */
    public function scopeOnReservationQueue(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->where('remaining_balance', '>', 0);
    }

    public function scopeDepositMet(Builder $query): Builder
    {
        return $query->whereRaw('(paid_amount + 0.009) >= (total_amount * 0.5)');
    }

    public function isFullyPaid(): bool
    {
        return ((float) $this->remaining_balance) <= 0.009;
    }

    public function scopeFullyPaid(Builder $query): Builder
    {
        return $query->where('remaining_balance', '<=', 0);
    }

    public function checkInWindowOpen(): bool
    {
        $now = now();
        $checkInDate = $this->check_in_date->toDateString();

        if ($now->toDateString() < $checkInDate) {
            return false;
        }

        if ($now->toDateString() > $checkInDate) {
            return true;
        }

        return $now->format('H:i') >= (string) config('resort.check_in_time', '14:00');
    }

    public function checkOutDue(): bool
    {
        $now = now();
        $checkOutDate = $this->check_out_date->toDateString();

        if ($now->toDateString() < $checkOutDate) {
            return false;
        }

        if ($now->toDateString() > $checkOutDate) {
            return true;
        }

        return $now->format('H:i') >= (string) config('resort.check_out_time', '12:00');
    }

    public function scopeCheckOutDue(Builder $query): Builder
    {
        $today = today()->toDateString();
        $checkoutFrom = (string) config('resort.check_out_time', '12:00');
        $pastCutoffToday = now()->format('H:i') >= $checkoutFrom;

        return $query->where(function (Builder $inner) use ($today, $pastCutoffToday) {
            $inner->whereDate('check_out_date', '<', $today);
            if ($pastCutoffToday) {
                $inner->orWhereDate('check_out_date', $today);
            }
        });
    }

    public function getShortNumberAttribute(): string
    {
        $number = (string) $this->booking_number;

        if (preg_match('/^BK-[A-Z0-9]{4}$/i', $number)) {
            return strtoupper($number);
        }

        if (preg_match('/-([A-Z0-9]+)$/i', $number, $matches)) {
            return 'BK-'.strtoupper($matches[1]);
        }

        return $number;
    }
}
