<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isGuestRole() ?? false;
    }

    public function rules(): array
    {
        $method = $this->input('payment_method');
        $needsProof = $method === 'gcash';

        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['gcash', 'cash'])],
            'reference_number' => [$needsProof ? 'required' : 'nullable', 'string', 'max:100'],
            'proof' => [$needsProof ? 'required' : 'nullable', 'image', 'max:5120'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference_number.required' => 'Please enter your GCash reference number.',
            'proof.required' => 'Please upload a screenshot of your payment proof.',
            'proof.image' => 'Payment proof must be an image (JPG, PNG, or WEBP).',
        ];
    }
}
