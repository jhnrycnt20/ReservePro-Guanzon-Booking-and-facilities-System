<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreBookingRequest;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Notifications\StaffNewReservationNotification;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected NotificationService $notificationService,
    ) {
    }

    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        // Include unpaid (paid_amount = 0) reservations so guests can still open and pay.
        $query = Booking::query()
            ->with([
                'accommodation' => fn ($q) => $q->withTrashed(),
                'payments',
            ])
            ->when(
                $guestId,
                fn ($builder) => $builder->where('guest_id', $guestId),
                fn ($builder) => $builder->whereRaw('0 = 1')
            )
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('booking_number', 'like', "%{$q}%")
                    ->orWhereHas('accommodation', function ($accommodationQuery) use ($q) {
                        $accommodationQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $bookings = $query->paginate(5)->withQueryString();

        return view('guest.bookings.index', compact('bookings'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $request->validate([
            'accommodation_id' => ['required', 'exists:accommodations,id'],
        ]);

        $accommodation = Accommodation::query()->with('type')->findOrFail($request->integer('accommodation_id'));

        $status = $accommodation->status instanceof \BackedEnum
            ? $accommodation->status->value
            : (string) $accommodation->status;

        if ($status === 'maintenance') {
            return redirect()
                ->route('accommodations.browse')
                ->with('error', 'Sorry, this room is under maintenance.');
        }

        return view('guest.bookings.create', [
            'accommodation' => $accommodation,
            'checkIn' => $request->query('check_in'),
            'checkOut' => $request->query('check_out'),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $guest = $request->user()->guest;
        abort_unless($guest, 403, 'Guest profile required.');

        $booking = $this->bookingService->createReservation(
            $request->validated(),
            $guest,
            $request->user()
        );

        $this->notificationService->notifyFrontDesk(new StaffNewReservationNotification($booking));

        return redirect()->route('guest.bookings.show', $booking);
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);
        $booking->load(['accommodation.type', 'items', 'payments', 'checkIn', 'checkOut', 'feedback', 'promo']);

        return view('guest.bookings.show', compact('booking'));
    }

    public function applyPromo(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $promoReturn = rtrim((string) url()->previous(), '#').'#rp-promo-section';

        try {
            $data = $request->validate([
                'promo_code' => ['required', 'string', 'max:32'],
            ]);

            $this->bookingService->applyPromo($booking, $data['promo_code']);
        } catch (ValidationException $exception) {
            return redirect()
                ->to($promoReturn)
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->to($promoReturn)
            ->with('success', 'Promo applied. Your balance has been updated.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookingService->cancel($booking, $request->user());

        return back();
    }
}
