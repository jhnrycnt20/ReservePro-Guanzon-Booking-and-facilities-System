<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with(['booking.guest.user', 'booking.accommodation', 'processor'])
            ->latest();

        $status = $request->input('status', 'pending');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('receipt_number', 'like', "%{$q}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($q) {
                        $bookingQuery->where('booking_number', 'like', "%{$q}%")
                            ->orWhere('guest_name', 'like', "%{$q}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('front_desk.payments.index', compact('payments', 'status'));
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);
        $payment->load(['booking.guest.user', 'booking.accommodation', 'processor', 'verifier']);

        return view('front_desk.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $this->paymentService->verifyPayment($payment, $request->user());

        return redirect()
            ->route('front_desk.payments.show', $payment)
            ->with('success', 'Payment verified.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $this->paymentService->rejectPayment($payment, $request->user(), $request->input('notes'));

        return redirect()
            ->route('front_desk.payments.show', $payment)
            ->with('success', 'Payment rejected.');
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.guest.user', 'verifier', 'processor']);

        return view('front_desk.payments.receipt', compact('payment'));
    }
}
