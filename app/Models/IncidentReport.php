<?php

namespace App\Models;

use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_number',
        'guest_id',
        'booking_id',
        'report_type',
        'title',
        'description',
        'location',
        'photo',
        'status',
        'security_guard_id',
        'investigation_notes',
        'investigation_photo',
        'invalid_reason',
        'front_desk_staff_id',
        'resolution_notes',
        'resolution_action',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'report_type' => IncidentType::class,
        'status' => IncidentStatus::class,
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function securityGuard(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_guard_id');
    }

    public function frontDeskStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'front_desk_staff_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(IncidentAttachment::class);
    }
}
