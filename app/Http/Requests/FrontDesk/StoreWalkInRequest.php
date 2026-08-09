<?php

namespace App\Http\Requests\FrontDesk;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalkInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFrontDesk() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'guest_id' => ['nullable', 'exists:guests,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'number_of_guests' => ['nullable', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,gcash,bank_transfer,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'auto_approve' => ['nullable', 'boolean'],
            'auto_check_in' => ['nullable', 'boolean'],
        ];
    }
}
