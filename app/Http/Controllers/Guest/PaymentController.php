<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\InitiateGcashPaymentRequest;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        $query = Payment::query()
            ->whereHas('booking', fn ($q) => $q->where('guest_id', $guestId))
            ->with('booking.accommodation')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('receipt_number', 'like', "%{$q}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($q) {
                        $bookingQuery->where('booking_number', 'like', "%{$q}%")
                            ->orWhereHas('accommodation', function ($accommodationQuery) use ($q) {
                                $accommodationQuery->where('name', 'like', "%{$q}%");
                            });
                    });
            });
        }

        $payments = $query->paginate(5)->withQueryString();

        return view('guest.payments.index', compact('payments'));
    }

    public function create(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);
        $booking = $this->paymentService->recalculateBalances($booking);

        $deposit = $this->paymentService->depositAmount($booking);
        $remaining = (float) $booking->remaining_balance;
        $suggestedDeposit = min($deposit, $remaining);

        return view('guest.payments.create', compact('booking', 'deposit', 'suggestedDeposit'));
    }

    public function show(Payment $payment): RedirectResponse
    {
        try {
            $this->authorize('view', $payment);

            $status = $payment->status instanceof PaymentStatus
                ? $payment->status
                : PaymentStatus::tryFrom((string) $payment->status);

            if ($status === PaymentStatus::Verified) {
                return redirect()->route('guest.payments.receipt', $payment);
            }

            $booking = $payment->booking;
            abort_unless($booking, 404);

            return redirect()
                ->route('guest.bookings.show', $booking)
                ->with('success', 'Open your booking to finish or review this payment.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            throw $exception;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('guest.payments.index')
                ->with('error', 'Could not open that payment. Please try again from My Payments or your booking.');
        }
    }

    public function store(InitiateGcashPaymentRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        try {
            $checkout = $this->paymentService->initiateGcashCheckout(
                $booking,
                (float) $request->validated('amount'),
                $request->user()
            );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('guest.payments.create', $booking)
                ->withErrors([
                    'amount' => 'Online GCash checkout is unavailable right now. Please try again later or pay at the front desk.',
                ]);
        }

        if (! $checkout['checkout_url']) {
            return redirect()
                ->route('guest.payments.create', $booking)
                ->withErrors(['amount' => 'Could not start the GCash checkout. Please try again.']);
        }

        return redirect()->away($checkout['checkout_url']);
    }

    public function checkoutReturn(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $queryStatus = $request->query('status') === 'success' ? 'success' : 'failed';
        $payment = null;

        if ($queryStatus === 'success') {
            $payment = $this->paymentService->syncPendingGcashCheckout($booking);
        }

        if ($payment?->status === PaymentStatus::Verified) {
            $status = 'confirmed';
        } elseif ($payment?->status === PaymentStatus::Rejected || $queryStatus === 'failed') {
            $status = 'failed';
        } else {
            $status = 'success';
        }

        $booking = $booking->fresh() ?? $booking;

        return view('guest.payments.gcash-return', compact('booking', 'status', 'payment'));
    }

    public function receipt(Payment $payment): View|RedirectResponse
    {
        try {
            $this->authorize('view', $payment);

            $status = $payment->status instanceof PaymentStatus
                ? $payment->status
                : PaymentStatus::tryFrom((string) $payment->status);

            abort_unless($status === PaymentStatus::Verified, 404);

            $payment->load(['booking.guest.user', 'booking.accommodation', 'verifier']);
            abort_unless($payment->booking, 404);

            return view('guest.payments.receipt', compact('payment'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $exception) {
            throw $exception;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('guest.payments.index')
                ->with('error', 'Could not open that receipt. Please try again from My Payments.');
        }
    }
}
