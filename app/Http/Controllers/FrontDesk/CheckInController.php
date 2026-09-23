<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CheckInService;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function __construct(protected CheckInService $checkInService)
    {
    }

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->with(['guest.user', 'accommodation' => fn ($q) => $q->withTrashed(), 'checkIn.staff'])
            ->where('status', BookingStatus::CheckedIn)
            ->whereHas('checkIn')
            ->orderByDesc('check_in_date')
            ->orderByDesc('id');

        ListFilters::applyBookingSearch($query, $request->input('q'));

        if ($request->filled('date')) {
            $query->whereDate('check_in_date', $request->input('date'));
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('front_desk.checkins.index', compact('bookings'));
    }

    public function show(Booking $booking): View|RedirectResponse
    {
        $booking->load([
            'guest.user',
            'accommodation' => fn ($q) => $q->withTrashed(),
            'payments.processor',
            'payments.verifier',
            'checkIn.staff',
            'promo',
        ]);

        if ($booking->status !== BookingStatus::CheckedIn || ! $booking->checkIn) {
            return redirect()
                ->route('front_desk.reservations.show', $booking)
                ->with('success', 'Check guests in from Reservations. This page only lists guests who are already checked in.');
        }

        return view('front_desk.checkins.show', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        try {
            $this->checkInService->checkIn($booking, $request->user(), $request->input('notes'));
        } catch (ValidationException $exception) {
            return redirect()
                ->route('front_desk.reservations.show', $booking)
                ->withErrors($exception->errors())
                ->withInput();
        }

        return redirect()
            ->route('front_desk.checkins.index')
            ->with('success', 'Guest checked in. They now appear under Check-in.');
    }
}
