<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreBookingRequest;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\StaffNewReservationNotification;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $bookings = Booking::query()
            ->with(['accommodation', 'payments'])
            ->where('guest_id', $request->user()->guest?->id)
            ->latest()
            ->paginate(15);

        return view('guest.bookings.index', compact('bookings'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $request->validate([
            'accommodation_id' => ['required', 'exists:accommodations,id'],
        ]);

        $accommodation = Accommodation::query()->with('type')->findOrFail($request->integer('accommodation_id'));

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

        User::query()
            ->whereHas('role', fn ($q) => $q->where('slug', 'front_desk'))
            ->where('is_active', true)
            ->get()
            ->each(fn (User $staff) => $this->notificationService->notify(
                $staff,
                new StaffNewReservationNotification($booking)
            ));

        return redirect()
            ->route('guest.bookings.show', $booking)
            ->with('success', 'Reservation submitted and placed in the front desk queue.');
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);
        $booking->load(['accommodation.type', 'items', 'payments', 'checkIn', 'checkOut', 'feedback']);

        return view('guest.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookingService->cancel($booking, $request->user());

        return back()->with('success', 'Reservation cancelled.');
    }
}
