<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\InitiateGcashPaymentRequest;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Accommodation;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\PromoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BookingService $bookingService,
        protected PromoService $promoService,
    ) {
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

    public function create(Request $request, Booking $booking): View|RedirectResponse|\Illuminate\Http\Response
    {
        $this->authorize('view', $booking);

        $completedBookingId = (int) $request->session()->get('guest_payment_success_booking_id', 0);
        if ($completedBookingId === (int) $booking->id && ! $request->boolean('fresh')) {
            return redirect()->route('guest.bookings.show', $booking);
        }
        if ($completedBookingId === (int) $booking->id && $request->boolean('fresh')) {
            $request->session()->forget('guest_payment_success_booking_id');

            return redirect()->route('guest.payments.create', $booking);
        }

        $booking = $this->paymentService->recalculateBalances($booking);

        if ((float) $booking->remaining_balance <= 0.009) {
            return redirect()->route('guest.bookings.show', $booking)
                ->with('success', 'This reservation has been paid in full.');
        }

        $deposit = $this->paymentService->depositAmount($booking);
        $remaining = (float) $booking->remaining_balance;
        $suggestedDeposit = min($deposit, $remaining);

        $isDraft = false;

        return response()->view('guest.payments.create', compact('booking', 'deposit', 'suggestedDeposit', 'isDraft'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function createDraft(Request $request): View|RedirectResponse|\Illuminate\Http\Response
    {
        $draft = $this->bookingDraft($request);
        if (! $draft) {
            return redirect()->route('guest.bookings.index')
                ->with('error', 'Your guest information was not found. Please start your reservation again.');
        }

        try {
            $preview = $this->previewDraft($draft['data']);
        } catch (ValidationException $exception) {
            // Stale/invalid promo on the draft should not crash billing.
            if (! empty($draft['data']['promo_code'])) {
                unset($draft['data']['promo_code']);
                $request->session()->put('guest_booking_draft', $draft);

                return redirect()->route('guest.payments.draft')
                    ->with('error', 'That promo is no longer valid. Continue without it or enter a new code.');
            }

            return redirect()->route('guest.bookings.create', array_filter([
                'accommodation_id' => $draft['data']['accommodation_id'] ?? null,
                'check_in' => $draft['data']['check_in_date'] ?? null,
                'check_out' => $draft['data']['check_out_date'] ?? null,
            ]))->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('guest.bookings.index')
                ->with('error', 'We could not open billing for this reservation. Please start again.');
        }

        $booking = $preview['booking'];
        $deposit = $this->paymentService->depositAmount($booking);
        $remaining = (float) $booking->remaining_balance;
        $suggestedDeposit = min($deposit, $remaining);
        $isDraft = true;

        return response()->view('guest.payments.create', compact('booking', 'deposit', 'suggestedDeposit', 'isDraft'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function applyDraftPromo(Request $request): RedirectResponse
    {
        $draft = $this->bookingDraft($request);
        if (! $draft) {
            return redirect()->route('guest.bookings.index')
                ->with('error', 'Your guest information was not found. Please start your reservation again.');
        }

        $validated = $request->validate(['promo_code' => ['required', 'string', 'max:32']]);

        try {
            $accommodationId = (int) $draft['data']['accommodation_id'];
            $promo = $this->promoService->findValidForAccommodation($validated['promo_code'], $accommodationId);
            $draft['data']['promo_code'] = $promo->code;
            $request->session()->put('guest_booking_draft', $draft);

            return redirect()->route('guest.payments.draft', [], 303)
                ->with('success', 'Promo code applied to your estimate.');
        } catch (ValidationException $exception) {
            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors($exception->errors());
        }
    }

    public function storeDraft(Request $request): RedirectResponse
    {
        $draft = $this->bookingDraft($request);
        if (! $draft) {
            return redirect()->route('guest.bookings.index')
                ->with('error', 'Your guest information was not found. Please start your reservation again.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
        ]);
        $preview = $this->previewDraft($draft['data'])['booking'];
        $amount = round((float) $validated['amount'], 2);
        $minimum = min($this->paymentService->depositAmount($preview), (float) $preview->remaining_balance);

        if ($amount + 0.009 < $minimum) {
            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors(['amount' => 'Minimum payment is ₱'.number_format($minimum, 2).' (50% deposit).']);
        }
        if ($amount - (float) $preview->remaining_balance > 0.009) {
            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors(['amount' => 'Payment amount cannot exceed the remaining balance.']);
        }

        $booking = null;
        try {
            $booking = $this->bookingService->createReservation(
                $draft['data'],
                $request->user()->guest,
                $request->user()
            );
            $checkout = $this->paymentService->initiateGcashCheckout($booking, $amount, $request->user());
        } catch (ValidationException $exception) {
            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            report($exception);
            if ($booking) {
                return redirect()->route('guest.payments.create', $booking)
                    ->withErrors(['amount' => 'We could not start GCash checkout. Your reservation is still pending and is not confirmed.']);
            }

            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors(['amount' => 'We could not start checkout. Please review your details and try again.']);
        }

        if (! $checkout['checkout_url']) {
            return redirect()->route('guest.payments.create', $booking)
                ->withErrors(['amount' => 'Could not start the GCash checkout. Your reservation is still pending and is not confirmed.']);
        }

        $request->session()->forget('guest_booking_draft');

        return redirect()->away($checkout['checkout_url']);
    }

    public function sampleSuccessDraft(Request $request): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $draft = $this->bookingDraft($request);
        if (! $draft) {
            return redirect()->route('guest.bookings.index')
                ->with('error', 'Your guest information was not found. Please start your reservation again.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
        ]);
        $preview = $this->previewDraft($draft['data'])['booking'];
        $amount = round((float) $validated['amount'], 2);
        $minimum = min($this->paymentService->depositAmount($preview), (float) $preview->remaining_balance);
        if ($amount + 0.009 < $minimum || $amount - (float) $preview->remaining_balance > 0.009) {
            return redirect()->route('guest.payments.draft')
                ->withInput()
                ->withErrors(['amount' => 'Enter an amount between ₱'.number_format($minimum, 2).' and ₱'.number_format((float) $preview->remaining_balance, 2).'.']);
        }

        $booking = $this->bookingService->createReservation(
            $draft['data'],
            $request->user()->guest,
            $request->user()
        );

        DB::transaction(function () use ($booking, $request, $amount) {
            Payment::query()->create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => 'gcash',
                'gateway' => 'demo',
                'gateway_status' => 'sample_success',
                'reference_number' => 'DEMO-'.strtoupper(Str::random(10)),
                'payment_date' => now(),
                'status' => PaymentStatus::Verified,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'processed_by' => $request->user()->id,
                'receipt_number' => $this->paymentService->generateReceiptNumber(),
                'notes' => 'Sample payment only. No real GCash transaction was made.',
            ]);
            $this->paymentService->recalculateBalances($booking);
        });

        $request->session()->forget('guest_booking_draft');
        $request->session()->put('guest_payment_success_booking_id', $booking->id);

        return redirect()->route('guest.bookings.show', $booking)
            ->with('success', 'Sample payment completed. No real charge was made.');
    }

    private function bookingDraft(Request $request): ?array
    {
        $draft = $request->session()->get('guest_booking_draft');

        return is_array($draft)
            && (int) ($draft['user_id'] ?? 0) === (int) $request->user()->id
            && is_array($draft['data'] ?? null)
                ? $draft
                : null;
    }

    private function previewDraft(array $data): array
    {
        $accommodation = Accommodation::query()->with('type')->findOrFail((int) $data['accommodation_id']);
        $totals = $this->bookingService->calculateTotal(
            $accommodation,
            $data['check_in_date'],
            $data['check_out_date']
        );
        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = $this->promoService->findValidForAccommodation((string) $data['promo_code'], (int) $accommodation->id);
            $totals = $this->promoService->applyToTotals($totals, $promo);
        }

        $booking = new Booking();
        $booking->forceFill([
            'accommodation_id' => $accommodation->id,
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'number_of_guests' => $data['number_of_guests'],
            'adults' => $data['adults'],
            'children' => $data['children'] ?? 0,
            'total_amount' => $totals['total'],
            'original_amount' => $totals['original_total'] ?? $totals['total'],
            'discount_amount' => $totals['discount_amount'] ?? 0,
            'promo_code' => $promo?->code,
            'paid_amount' => 0,
            'remaining_balance' => $totals['total'],
        ]);
        $booking->setRelation('accommodation', $accommodation);

        return ['booking' => $booking, 'totals' => $totals];
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

    /** Create a clearly marked fake payment for local UI demonstrations only. */
    public function sampleSuccess(InitiateGcashPaymentRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $this->authorize('view', $booking);

        $user = $request->user();
        $amount = round((float) $request->validated('amount'), 2);

        $payment = DB::transaction(function () use ($booking, $user, $amount) {
            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => 'gcash',
                'gateway' => 'demo',
                'gateway_status' => 'sample_success',
                'reference_number' => 'DEMO-'.strtoupper(Str::random(10)),
                'payment_date' => now(),
                'status' => PaymentStatus::Verified,
                'verified_by' => $user->id,
                'verified_at' => now(),
                'processed_by' => $user->id,
                'receipt_number' => $this->paymentService->generateReceiptNumber(),
                'notes' => 'Sample payment only. No real GCash transaction was made.',
            ]);

            $this->paymentService->recalculateBalances($booking);

            return $payment;
        });

        $request->session()->put('guest_payment_success_booking_id', $booking->id);

        return redirect()->route('guest.bookings.show', $booking)
            ->with('success', 'Sample payment completed. No real charge was made.');
    }

    public function checkoutReturn(Request $request, Booking $booking): View|RedirectResponse
    {
        $this->authorize('view', $booking);

        $queryStatus = $request->query('status') === 'success' ? 'success' : 'failed';
        $payment = null;

        if ($queryStatus === 'success') {
            $payment = $this->paymentService->syncPendingGcashCheckout($booking);
        }

        if ($payment?->status === PaymentStatus::Verified) {
            $request->session()->put('guest_payment_success_booking_id', $booking->id);

            return redirect()
                ->route('guest.bookings.show', $booking)
                ->with('success', 'Your GCash payment was confirmed.');
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
