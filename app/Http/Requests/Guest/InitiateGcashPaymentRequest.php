<?php

namespace App\Http\Requests\Guest;

use App\Services\PaymentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InitiateGcashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isGuestRole() ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $booking = $this->route('booking');
            if (! $booking) {
                return;
            }

            $paymentService = app(PaymentService::class);
            $booking = $paymentService->recalculateBalances($booking);

            $remaining = round((float) $booking->remaining_balance, 2);
            $amount = round((float) $this->input('amount'), 2);

            if ($amount - $remaining > 0.009) {
                $validator->errors()->add(
                    'amount',
                    'Payment amount cannot exceed the remaining balance of ₱'.number_format($remaining, 2).'.'
                );

                return;
            }

            // While the 50% deposit is not met, reject payments below the amount still needed for deposit.
            if (! $booking->hasMetDepositRequirement()) {
                $deposit = round($paymentService->depositAmount($booking), 2);
                $paid = round((float) $booking->paid_amount, 2);
                $minRequired = round(min(max($deposit - $paid, 0.01), $remaining), 2);

                if ($amount + 0.009 < $minRequired) {
                    $validator->errors()->add(
                        'amount',
                        'Minimum payment is ₱'.number_format($minRequired, 2).' (50% deposit).'
                    );
                }
            }
        });
    }
}
