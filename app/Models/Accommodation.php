<?php

namespace App\Models;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
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
        'gallery',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'rate' => 'decimal:2',
        'status' => AccommodationStatus::class,
        'is_active' => 'boolean',
        'gallery' => 'array',
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

    /**
     * True when an active reservation or check-in owns this room.
     * Admin must not override status while the room is in use.
     */
    public function isStatusLocked(): bool
    {
        $status = $this->status instanceof AccommodationStatus
            ? $this->status
            : AccommodationStatus::tryFrom((string) $this->status);

        if ($status === AccommodationStatus::Occupied) {
            return true;
        }

        return $this->bookings()
            ->whereIn('status', [
                BookingStatus::Approved,
                BookingStatus::CheckedIn,
            ])
            ->exists();
    }

    public function statusLockReason(): ?string
    {
        if (! $this->isStatusLocked()) {
            return null;
        }

        $status = $this->status instanceof AccommodationStatus
            ? $this->status
            : AccommodationStatus::tryFrom((string) $this->status);

        if ($status === AccommodationStatus::Occupied
            || $this->bookings()->where('status', BookingStatus::CheckedIn)->exists()) {
            return 'Status is locked while a guest is checked in.';
        }

        return 'Status is locked while this room has an active booking.';
    }

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(Promo::class);
    }

    /**
     * Shared key for numbered units of the same product (e.g. CAB-01 / CAB-02 → CAB).
     */
    public function familyKey(): string
    {
        $number = trim((string) $this->number);
        if ($number !== '' && preg_match('/^(.*)-(\d+)$/', $number, $matches)) {
            return $matches[1];
        }

        return $number !== '' ? $number : 'acc-'.$this->getKey();
    }

    public function unitNumber(): ?int
    {
        if (preg_match('/-(\d+)$/', (string) $this->number, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Card/title name without the unit suffix (Cabana 1 → Cabana).
     */
    public function baseDisplayName(): string
    {
        $name = trim((string) $this->name);
        $stripped = preg_replace('/\s+\d+$/', '', $name);

        return is_string($stripped) && $stripped !== '' ? $stripped : $name;
    }

    /**
     * Active sibling units in the same family, ordered by unit number.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public function siblingUnits()
    {
        $family = $this->familyKey();

        return static::query()
            ->where('is_active', true)
            ->where('accommodation_type_id', $this->accommodation_type_id)
            ->get()
            ->filter(fn (self $unit) => $unit->familyKey() === $family)
            ->sortBy(fn (self $unit) => sprintf('%05d-%s', $unit->unitNumber() ?? 0, $unit->number))
            ->values();
    }

    /**
     * One representative row per product family (lowest unit number).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeRepresentatives($query)
    {
        $ids = static::query()
            ->where('is_active', true)
            ->get(['id', 'number', 'accommodation_type_id', 'name'])
            ->groupBy(fn (self $unit) => $unit->familyKey())
            ->map(function ($group) {
                return $group->sortBy(fn (self $unit) => sprintf('%05d-%s', $unit->unitNumber() ?? 0, $unit->number))->first()?->id;
            })
            ->filter()
            ->values()
            ->all();

        return $query->whereIn($query->getModel()->getQualifiedKeyName(), $ids ?: [0]);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'images/')) {
                return asset($this->image);
            }

            return asset('storage/'.$this->image);
        }

        $gallery = $this->gallery_urls;
        if ($gallery !== []) {
            return $gallery[0];
        }

        return asset('images/rooms/ocean-view-room.png');
    }

    /**
     * @return list<string>
     */
    public function getGalleryUrlsAttribute(): array
    {
        $paths = collect($this->gallery ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values();

        if ($this->image) {
            $paths = $paths->prepend($this->image)->unique()->values();
        }

        return $paths->map(function (string $path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, 'images/')) {
                return asset($path);
            }

            return asset('storage/'.$path);
        })->all();
    }

    /**
     * Raw gallery paths paired with their resolved URL, for admin gallery management.
     *
     * @return list<array{path: string, url: string}>
     */
    public function getGalleryItemsAttribute(): array
    {
        return collect($this->gallery ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->map(fn (string $path) => [
                'path' => $path,
                'url' => match (true) {
                    str_starts_with($path, 'http://'), str_starts_with($path, 'https://') => $path,
                    str_starts_with($path, 'images/') => asset($path),
                    default => asset('storage/'.$path),
                },
            ])
            ->all();
    }
}
