<?php

namespace App\Http\Controllers\Guest;

use App\Enums\BookingStatus;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\IncidentReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        $bookingQuery = Booking::query()->when(
            $guestId,
            fn ($query) => $query->where('guest_id', $guestId),
            fn ($query) => $query->whereRaw('1 = 0')
        );

        $recentBookings = (clone $bookingQuery)
            ->with('accommodation')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'bookings' => (clone $bookingQuery)->count(),
            'pending' => (clone $bookingQuery)->where('status', BookingStatus::Pending)->count(),
            'checked_in' => (clone $bookingQuery)->where('status', BookingStatus::CheckedIn)->count(),
            'open_reports' => IncidentReport::query()
                ->when(
                    $guestId,
                    fn ($query) => $query->where('guest_id', $guestId),
                    fn ($query) => $query->whereRaw('1 = 0')
                )
                ->whereNotIn('status', [IncidentStatus::Resolved, IncidentStatus::Closed, IncidentStatus::Invalid])
                ->count(),
        ];

        return view('guest.dashboard', compact('stats', 'recentBookings'));
    }
}
