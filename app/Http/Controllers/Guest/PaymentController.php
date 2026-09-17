<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StorePaymentRequest;
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

    public function store(StorePaymentRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $payment = $this->paymentService->recordPayment(
            $booking,
            array_merge($request->validated(), ['auto_verify' => true]),
            $request->user(),
            $request->file('proof')
        );

        return redirect()
            ->route('guest.bookings.show', $booking)
            ->with('success', 'Payment recorded. Your balance has been updated.');
    }
}
