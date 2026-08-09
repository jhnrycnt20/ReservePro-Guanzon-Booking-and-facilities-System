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

    public function index(): View
    {
        $payments = Payment::query()
            ->with(['booking.guest.user', 'processor'])
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->paginate(20);

        return view('front_desk.payments.index', compact('payments'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $this->paymentService->verifyPayment($payment, $request->user());

        return back()->with('success', 'Payment verified.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('verify', $payment);
        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $this->paymentService->rejectPayment($payment, $request->user(), $request->input('notes'));

        return back()->with('success', 'Payment rejected.');
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.guest.user', 'verifier', 'processor']);

        return view('front_desk.payments.receipt', compact('payment'));
    }
}
