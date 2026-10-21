<?php

namespace App\Http\Requests\Guest;

use App\Models\Accommodation;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isGuestRole() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $adults = $this->input('adults', 1);
        $children = $this->input('children', 0);

        // Derive the total without turning invalid guest counts into valid ones.
        $this->merge(['adults' => $adults, 'children' => $children]);
        if (filter_var($adults, FILTER_VALIDATE_INT) !== false
            && filter_var($children, FILTER_VALIDATE_INT) !== false) {
            $this->merge(['number_of_guests' => (int) $adults + (int) $children]);
        }
    }

    public function rules(): array
    {
        return [
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'promo_code' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('adults') || $validator->errors()->has('children')
                || $validator->errors()->has('accommodation_id')) {
                return;
            }

            $accommodation = Accommodation::query()->find($this->input('accommodation_id'));
            if (! $accommodation) {
                return;
            }

            $total = (int) $this->input('adults', 0) + (int) $this->input('children', 0);
            if ($total > $accommodation->capacity) {
                $validator->errors()->add(
                    'adults',
                    "Total guests ({$total}) exceeds this accommodation's capacity of {$accommodation->capacity}."
                );
            }
        });
    }
}
