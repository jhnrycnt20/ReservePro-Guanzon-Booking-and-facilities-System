<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CheckOutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckOutController extends Controller
{
    public function __construct(protected CheckOutService $checkOutService)
    {
    }

    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['guest.user', 'accommodation'])
            ->where('status', BookingStatus::CheckedIn)
            ->orderBy('check_out_date')
            ->paginate(20);

        return view('front_desk.checkouts.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['guest.user', 'accommodation', 'payments', 'checkIn', 'checkOut']);

        return view('front_desk.checkouts.show', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'additional_charges' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->checkOutService->checkOut(
            $booking,
            $request->user(),
            (float) ($data['additional_charges'] ?? 0),
            $data['notes'] ?? null
        );

        return redirect()
            ->route('front_desk.dashboard')
            ->with('success', 'Guest checked out. Accommodation is now Available.');
    }
}
