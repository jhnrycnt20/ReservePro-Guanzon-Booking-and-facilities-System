<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with(['booking.guest.user', 'booking.accommodation', 'processor'])
            ->latest();

        $status = $request->input('status', 'all');
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

    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.guest.user', 'verifier', 'processor']);

        return view('front_desk.payments.receipt', compact('payment'));
    }
}
