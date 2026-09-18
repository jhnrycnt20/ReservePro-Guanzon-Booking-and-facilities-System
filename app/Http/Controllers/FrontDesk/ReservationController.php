<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\RejectBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->with(['guest.user', 'accommodation' => fn ($q) => $q->withTrashed()])
            ->frontDeskReservations();

        if ($request->filled('q')) {
            ListFilters::applyBookingSearch($query, $request->input('q'));
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        return view('front_desk.reservations.index', compact('bookings'));
    }

    public function show(Booking $booking): View|RedirectResponse
    {
        if (in_array($booking->status, [BookingStatus::Rejected, BookingStatus::Cancelled], true)) {
            return redirect()
                ->route('front_desk.reservations.index')
                ->with('success', 'This reservation is no longer available.');
        }

        if ($booking->status === BookingStatus::CheckedOut) {
            return redirect()
                ->route('front_desk.reservations.index')
                ->with('success', 'This stay is already checked out.');
        }

        $booking->load([
            'guest.user',
            'accommodation' => fn ($q) => $q->withTrashed(),
            'items',
            'payments.processor',
            'payments.verifier',
            'checkIn',
            'checkOut',
            'promo',
        ]);

        return view('front_desk.reservations.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('approve', $booking);
        $this->bookingService->approve($booking, $request->user());

        return back()->with('success', 'Reservation approved.');
    }

    public function reject(RejectBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('reject', $booking);
        $this->bookingService->reject($booking, $request->user(), $request->validated('rejection_reason'));

        return redirect()
            ->route('front_desk.reservations.index')
            ->with('success', 'Reservation rejected and removed from the queue.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookingService->cancel($booking, $request->user());

        return back()->with('success', 'Reservation cancelled.');
    }
}
