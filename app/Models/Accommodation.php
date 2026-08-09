<?php

namespace App\Models;

use App\Enums\AccommodationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'accommodation_type_id',
        'name',
        'number',
        'description',
        'capacity',
        'rate',
        'status',
        'image',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'rate' => 'decimal:2',
        'status' => AccommodationStatus::class,
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(AccommodationType::class, 'accommodation_type_id');
    }

    public function accommodationType(): BelongsTo
    {
        return $this->type();
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenity')->withTimestamps();
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(Pricing::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'images/')) {
                return asset($this->image);
            }

            return asset('storage/'.$this->image);
        }

        return asset('images/rooms/ocean-view-room.png');
    }
}
