<?php

namespace App\Http\Requests\FrontDesk;

use App\Models\Accommodation;
use App\Services\BookingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFrontDesk() || $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $adults = $this->input('adults', 1);
        $children = $this->input('children', 0);

        $this->merge(['adults' => $adults, 'children' => $children]);
        if (filter_var($adults, FILTER_VALIDATE_INT) !== false
            && filter_var($children, FILTER_VALIDATE_INT) !== false) {
            $this->merge(['number_of_guests' => (int) $adults + (int) $children]);
        }

        // Support legacy single-payment fields by converting to payments[].
        if (! $this->filled('payments') && $this->filled('payment_amount')) {
            $this->merge([
                'payments' => [[
                    'payment_method' => $this->input('payment_method', 'cash'),
                    'amount' => $this->input('payment_amount'),
                    'reference_number' => $this->input('reference_number'),
                ]],
            ]);
        }

        $payments = $this->input('payments');
        if (is_array($payments)) {
            $normalized = [];
            foreach ($payments as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $method = strtolower(trim((string) ($row['payment_method'] ?? 'cash')));
                $ref = trim((string) ($row['reference_number'] ?? ''));
                $normalized[] = [
                    'payment_method' => $method !== '' ? $method : 'cash',
                    'amount' => $row['amount'] ?? null,
                    'reference_number' => $ref !== '' ? $ref : null,
                ];
            }
            $this->merge(['payments' => $normalized]);
        }
    }

    public function rules(): array
    {
        return [
            'guest_id' => ['nullable', 'exists:guests,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'accommodation_id' => ['required', 'exists:accommodations,id'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'payments' => ['required', 'array', 'min:1', 'max:3'],
            'payments.*.payment_method' => ['required', Rule::in(['cash', 'gcash'])],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'payments.required' => 'Add at least one payment that covers the full stay total.',
            'payments.min' => 'Add at least one payment that covers the full stay total.',
            'payments.max' => 'You can split into at most 3 payments.',
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

            $totalGuests = (int) $this->input('adults', 0) + (int) $this->input('children', 0);
            if ($totalGuests > $accommodation->capacity) {
                $validator->errors()->add(
                    'adults',
                    "Total guests ({$totalGuests}) exceeds this accommodation's capacity of {$accommodation->capacity}."
                );
            }

            $payments = collect($this->input('payments', []))
                ->filter(fn ($row) => is_array($row))
                ->values();

            foreach ($payments as $index => $row) {
                $method = (string) ($row['payment_method'] ?? '');
                $ref = trim((string) ($row['reference_number'] ?? ''));
                if ($method === 'gcash' && $ref === '') {
                    $validator->errors()->add(
                        "payments.{$index}.reference_number",
                        'Reference number is required for GCash.'
                    );
                }
            }

            if ($validator->errors()->hasAny(['check_in_date', 'check_out_date', 'payments', 'accommodation_id'])) {
                return;
            }

            try {
                $stayTotal = (float) app(BookingService::class)->calculateTotal(
                    $accommodation,
                    (string) $this->input('check_in_date'),
                    (string) $this->input('check_out_date')
                )['total'];
            } catch (\Throwable) {
                return;
            }

            $paid = round($payments->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);
            if (abs($paid - $stayTotal) > 0.009) {
                $validator->errors()->add(
                    'payments',
                    'Walk-ins require full payment of ₱'.number_format($stayTotal, 2)
                    .' (entered ₱'.number_format($paid, 2).'). Split Cash + GCash is allowed if the total matches.'
                );
            }
        });
    }
}
