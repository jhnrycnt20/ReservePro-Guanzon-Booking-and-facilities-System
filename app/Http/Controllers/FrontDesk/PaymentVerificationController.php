<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
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

    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.guest.user', 'verifier', 'processor']);

        return view('front_desk.payments.receipt', compact('payment'));
    }
}
