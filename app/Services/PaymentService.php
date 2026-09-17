<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentRecordedNotification;
use App\Notifications\PaymentVerifiedNotification;
use App\Notifications\StaffPaymentRecordedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public const DEPOSIT_PERCENT = 50;

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

    public function depositAmount(Booking $booking): float
    {
        return round(((float) $booking->total_amount) * (self::DEPOSIT_PERCENT / 100), 2);
    }

    public function hasVerifiedDeposit(Booking $booking): bool
    {
        $booking = $this->recalculateBalances($booking);

        return (float) $booking->paid_amount + 0.009 >= $this->depositAmount($booking);
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

    public function recordPayment(Booking $booking, array $data, User $processor, ?UploadedFile $proof = null): Payment
    {
        if (in_array($booking->status->value, ['rejected', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'booking_id' => 'Cannot record payment for a rejected or cancelled booking.',
            ]);
        }

        $amount = round((float) $data['amount'], 2);
        $remaining = (float) $booking->remaining_balance;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount must be greater than zero.',
            ]);
        }

        if ($amount - $remaining > 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount cannot exceed the remaining balance.',
            ]);
        }

        $method = is_object($data['payment_method'] ?? null)
            ? $data['payment_method']->value
            : (string) ($data['payment_method'] ?? 'cash');

        if (in_array($method, ['gcash', 'bank_transfer'], true) && empty($data['reference_number'])) {
            throw ValidationException::withMessages([
                'reference_number' => 'Reference number is required for GCash and bank transfer.',
            ]);
        }

        return DB::transaction(function () use ($booking, $data, $processor, $amount, $proof, $method) {
            // Only auto-verify when explicitly requested (walk-in / front desk cash).
            $autoVerify = (bool) ($data['auto_verify'] ?? false);

            $proofPath = null;
            if ($proof) {
                $proofPath = $proof->store('payment-proofs', 'public');
            }

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => $method,
                'reference_number' => $data['reference_number'] ?? null,
                'proof_path' => $proofPath,
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
                app(FrontDeskAutomationService::class)->syncBooking($booking->fresh(), $processor);
            }

            $this->auditService->log('payment.recorded', $payment, null, $payment->toArray(), $processor);

            if ($booking->guest?->user) {
                $this->notificationService->notify(
                    $booking->guest->user,
                    new PaymentRecordedNotification($booking)
                );
            }

            if ($autoVerify) {
                $booking->loadMissing('accommodation');
                $this->notificationService->notifyFrontDesk(
                    new StaffPaymentRecordedNotification($booking->fresh(), $amount)
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
            app(FrontDeskAutomationService::class)->syncBooking($payment->booking->fresh(), $verifier);

            $this->auditService->log('payment.verified', $payment, $old, $payment->fresh()->toArray(), $verifier);

            if ($payment->booking?->guest?->user) {
                $this->notificationService->notify(
                    $payment->booking->guest->user,
                    new PaymentVerifiedNotification($payment->booking)
                );
            }

            $payment->booking?->loadMissing('accommodation');
            if ($payment->booking) {
                $this->notificationService->notifyFrontDesk(
                    new StaffPaymentRecordedNotification($payment->booking, (float) $payment->amount)
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
