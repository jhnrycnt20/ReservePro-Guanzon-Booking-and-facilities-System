<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CheckInService;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(protected CheckInService $checkInService)
    {
    }

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->with(['guest.user', 'accommodation' => fn ($q) => $q->withTrashed(), 'payments'])
            ->where('status', BookingStatus::Approved)
            ->orderBy('check_in_date');

        ListFilters::applyBookingSearch($query, $request->input('q'));

        if ($request->filled('date')) {
            $query->whereDate('check_in_date', $request->input('date'));
        }

        if ($request->input('balance') === 'unpaid') {
            $query->where('remaining_balance', '>', 0);
        } elseif ($request->input('balance') === 'paid') {
            $query->where('remaining_balance', '<=', 0);
        }

        $bookings = $query->paginate(20)->withQueryString();

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
