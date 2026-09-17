<?php

namespace App\Http\Requests\FrontDesk;

use App\Models\Accommodation;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFrontDesk() || $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $adults = max(1, (int) $this->input('adults', 1));
        $children = max(0, (int) $this->input('children', 0));

        $this->merge([
            'adults' => $adults,
            'children' => $children,
            'number_of_guests' => $adults + $children,
        ]);
    }

    public function rules(): array
    {
        return [
            'guest_id' => ['nullable', 'exists:guests,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'in:cash,gcash'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
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
