<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentRecordedNotification;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService,
    ) {
    }

    public function generateReceiptNumber(): string
    {
        do {
            $number = 'RCP-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
        } while (Payment::withTrashed()->where('receipt_number', $number)->exists());

        return $number;
    }

    public function recalculateBalances(Booking $booking): Booking
    {
        $paid = (float) $booking->payments()
            ->where('status', PaymentStatus::Verified->value)
            ->sum('amount');

        $total = (float) $booking->total_amount;
        $remaining = max(0, round($total - $paid, 2));

        $booking->update([
            'paid_amount' => max(0, round($paid, 2)),
            'remaining_balance' => $remaining,
        ]);

        return $booking->fresh();
    }

    public function recordPayment(Booking $booking, array $data, User $processor): Payment
    {
        if (in_array($booking->status->value, ['rejected', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'booking_id' => 'Cannot record payment for a rejected or cancelled booking.',
            ]);
        }

        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($booking, $data, $processor, $amount) {
            $autoVerify = (bool) ($data['auto_verify'] ?? false)
                || ($processor->isFrontDesk() || $processor->isAdmin());

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date' => $data['payment_date'] ?? now(),
                'status' => $autoVerify ? PaymentStatus::Verified : PaymentStatus::Pending,
                'notes' => $data['notes'] ?? null,
                'processed_by' => $processor->id,
                'verified_by' => $autoVerify ? $processor->id : null,
                'verified_at' => $autoVerify ? now() : null,
                'receipt_number' => $autoVerify ? $this->generateReceiptNumber() : null,
            ]);

            if ($autoVerify) {
                $this->recalculateBalances($booking);
            }

            $this->auditService->log('payment.recorded', $payment, null, $payment->toArray(), $processor);

            if ($booking->guest?->user) {
                $this->notificationService->notify(
                    $booking->guest->user,
                    new PaymentRecordedNotification($booking)
                );
            }

            return $payment->fresh();
        });
    }

    public function verifyPayment(Payment $payment, User $verifier): Payment
    {
        if ($payment->status !== PaymentStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payments can be verified.',
            ]);
        }

        return DB::transaction(function () use ($payment, $verifier) {
            $old = $payment->toArray();

            $payment->update([
                'status' => PaymentStatus::Verified,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'receipt_number' => $payment->receipt_number ?: $this->generateReceiptNumber(),
            ]);

            $this->recalculateBalances($payment->booking);

            $this->auditService->log('payment.verified', $payment, $old, $payment->fresh()->toArray(), $verifier);

            if ($payment->booking?->guest?->user) {
                $this->notificationService->notify(
                    $payment->booking->guest->user,
                    new PaymentVerifiedNotification($payment->booking)
                );
            }

            return $payment->fresh();
        });
    }

    public function rejectPayment(Payment $payment, User $verifier, ?string $notes = null): Payment
    {
        if ($payment->status !== PaymentStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending payments can be rejected.',
            ]);
        }

        return DB::transaction(function () use ($payment, $verifier, $notes) {
            $old = $payment->toArray();

            $payment->update([
                'status' => PaymentStatus::Rejected,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'notes' => $notes ?? $payment->notes,
            ]);

            $this->auditService->log('payment.rejected', $payment, $old, $payment->fresh()->toArray(), $verifier);

            return $payment->fresh();
        });
    }
}
