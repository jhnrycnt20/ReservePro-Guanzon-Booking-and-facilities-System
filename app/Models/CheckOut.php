<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'staff_id',
        'checked_out_at',
        'additional_charges',
        'final_balance',
        'notes',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'additional_charges' => 'decimal:2',
        'final_balance' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
