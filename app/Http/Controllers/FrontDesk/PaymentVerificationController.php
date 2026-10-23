<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('front_desk.reservations.index', $request->query());
    }

    public function show(Payment $payment): RedirectResponse
    {
        $this->authorize('view', $payment);

        $booking = $payment->booking;
        if (! $booking) {
            return redirect()->route('front_desk.reservations.index');
        }

        return redirect()->route('front_desk.reservations.show', $booking);
    }

    public function collect(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved, BookingStatus::CheckedIn], true)) {
            return back()->withErrors(['amount' => 'Payments can only be collected for active stays.']);
        }

        if ($booking->isFullyPaid()) {
            return back()->with('success', 'This reservation is already fully paid.');
        }

        $remaining = round((float) $booking->remaining_balance, 2);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'payment_method' => ['required', Rule::in(['cash', 'gcash'])],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Walk-ins must settle in full — no partial balance collections.
        if ($booking->is_walk_in && round((float) $data['amount'], 2) + 0.009 < $remaining) {
            return back()
                ->withErrors([
                    'amount' => 'Walk-ins require full payment. Collect the remaining ₱'.number_format($remaining, 2).'.',
                ])
                ->withInput();
        }

        try {
            $this->paymentService->recordPayment($booking, [
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_date' => now(),
                'auto_verify' => true,
            ], $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $booking->refresh();
        $message = $booking->isFullyPaid()
            ? 'Balance collected. Reservation is Ready for check-in.'
            : 'Payment recorded. Remaining balance: ₱'.number_format((float) $booking->remaining_balance, 2).'.';

        if ($booking->status === BookingStatus::CheckedIn) {
            $message = $booking->isFullyPaid()
                ? 'Balance collected. You can proceed to check-out.'
                : 'Payment recorded. Remaining balance: ₱'.number_format((float) $booking->remaining_balance, 2).'.';
        }

        return redirect()
            ->route('front_desk.reservations.index', [
                'tab' => $booking->status === BookingStatus::CheckedIn ? 'check_out' : 'all',
            ])
            ->with('open_view', $booking->id)
            ->with('success', $message);
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.guest.user', 'verifier', 'processor']);

        return view('front_desk.payments.receipt', compact('payment'));
    }
}
