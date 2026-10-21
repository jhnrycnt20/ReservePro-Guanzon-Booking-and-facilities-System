<?php

namespace App\Http\Requests\Guest;

use App\Enums\IncidentType;
use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isGuestRole() ?? false;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['nullable', Rule::exists('bookings', 'id')
                ->where('guest_id', $this->user()?->guest?->id ?? 0)
                ->whereNull('deleted_at')
                ->whereIn('status', [
                    BookingStatus::Approved->value,
                    BookingStatus::CheckedIn->value,
                    BookingStatus::CheckedOut->value,
                ])],
            'report_type' => ['required', Rule::enum(IncidentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
