<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\StaffPaymentVerificationNotification;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        $payments = Payment::query()
            ->whereHas('booking', fn ($q) => $q->where('guest_id', $guestId))
            ->with('booking')
            ->latest()
            ->paginate(15);

        return view('guest.payments.index', compact('payments'));
    }

    public function create(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        return view('guest.payments.create', compact('booking'));
    }

    public function store(StorePaymentRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $payment = $this->paymentService->recordPayment(
            $booking,
            array_merge($request->validated(), ['auto_verify' => false]),
            $request->user()
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
            ->with('success', 'Payment recorded and awaiting front desk verification.');
    }
}
