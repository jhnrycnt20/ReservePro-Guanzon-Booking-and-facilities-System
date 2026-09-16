<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Enums\PaymentStatus;
use App\Http\Requests\Guest\StoreGcashCheckoutRequest;
use App\Http\Requests\Guest\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\StaffPaymentVerificationNotification;
use App\Services\NotificationService;
use App\Services\PayMongoService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected NotificationService $notificationService,
        protected PayMongoService $payMongo,
    ) {
    }

    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        $payments = Payment::query()
            ->whereHas('booking', fn ($q) => $q->where('guest_id', $guestId))
            ->with('booking.accommodation')
            ->latest()
            ->paginate(5);

        return view('guest.payments.index', compact('payments'));
    }

    public function create(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $deposit = $this->paymentService->depositAmount($booking);
        $remaining = (float) $booking->remaining_balance;
        $suggestedDeposit = min($deposit, $remaining);

        $paymongoEnabled = $this->payMongo->isConfigured();

        return view('guest.payments.create', compact('booking', 'deposit', 'suggestedDeposit', 'paymongoEnabled'));
    }

    public function store(StorePaymentRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $payment = $this->paymentService->recordPayment(
            $booking,
            array_merge($request->validated(), ['auto_verify' => false]),
            $request->user(),
            $request->file('proof')
        );

        User::query()
            ->whereHas('role', fn ($q) => $q->where('slug', 'front_desk'))
            ->where('is_active', true)
            ->get()
            ->each(fn (User $staff) => $this->notificationService->notify(
                $staff,
                new StaffPaymentVerificationNotification($booking)
            ));

        return redirect()
            ->route('guest.bookings.show', $booking)
            ->with('success', 'Payment submitted. Front desk will verify it shortly.');
    }

    public function gcashCheckout(StoreGcashCheckoutRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        if (! $this->payMongo->isConfigured()) {
            return redirect()
                ->route('guest.payments.create', $booking)
                ->withErrors(['payment' => 'Online GCash is not available. Use manual payment instead.']);
        }

        $amount = round((float) $request->validated('amount'), 2);

        $payment = $this->paymentService->createPayMongoPendingPayment(
            $booking,
            $amount,
            $request->user()
        );

        $checkout = $this->payMongo->createGcashCheckout($payment, $booking, $request->user());

        $payment->update([
            'gateway_ref' => $checkout['payment_intent_id'],
            'gateway_checkout_url' => $checkout['checkout_url'],
        ]);

        return redirect()->away($checkout['checkout_url']);
    }

    public function gcashReturn(Request $request): RedirectResponse
    {
        $payment = Payment::query()->with('booking')->findOrFail($request->query('payment'));
        $this->authorize('view', $payment->booking);

        if ($request->boolean('failed')) {
            return redirect()
                ->route('guest.bookings.show', $payment->booking)
                ->with('error', 'GCash payment was not completed. You can try again.');
        }

        if ($payment->status === PaymentStatus::Verified) {
            return redirect()
                ->route('guest.bookings.show', $payment->booking)
                ->with('success', 'Payment received and verified. Thank you!');
        }

        return redirect()
            ->route('guest.bookings.show', $payment->booking)
            ->with('success', 'If you completed payment in GCash, it will be verified automatically in a moment.');
    }
}
