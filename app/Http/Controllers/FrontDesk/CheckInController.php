<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(protected CheckInService $checkInService)
    {
    }

    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['guest.user', 'accommodation', 'payments'])
            ->where('status', BookingStatus::Approved)
            ->orderBy('check_in_date')
            ->paginate(20);

        return view('front_desk.checkins.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['guest.user', 'accommodation', 'payments', 'checkIn']);

        return view('front_desk.checkins.show', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        $this->checkInService->checkIn($booking, $request->user(), $request->input('notes'));

        return redirect()
            ->route('front_desk.dashboard')
            ->with('success', 'Guest checked in. Accommodation is now Occupied.');
    }
}
