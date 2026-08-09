<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\RejectBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
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
        $query = Booking::query()->with(['guest.user', 'accommodation']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->latest()->paginate(20);

        return view('front_desk.reservations.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['guest.user', 'accommodation', 'items', 'payments', 'checkIn', 'checkOut']);

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

        return back()->with('success', 'Reservation rejected.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookingService->cancel($booking, $request->user());

        return back()->with('success', 'Reservation cancelled.');
    }
}
