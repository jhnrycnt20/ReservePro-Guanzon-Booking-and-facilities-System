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

        $recentBookings = Booking::query()
            ->with('accommodation')
            ->where('guest_id', $guestId)
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'bookings' => Booking::query()->where('guest_id', $guestId)->count(),
            'pending' => Booking::query()->where('guest_id', $guestId)->where('status', BookingStatus::Pending)->count(),
            'checked_in' => Booking::query()->where('guest_id', $guestId)->where('status', BookingStatus::CheckedIn)->count(),
            'open_reports' => IncidentReport::query()
                ->where('guest_id', $guestId)
                ->whereNotIn('status', [IncidentStatus::Resolved, IncidentStatus::Closed, IncidentStatus::Invalid])
                ->count(),
        ];

        return view('guest.dashboard', compact('stats', 'recentBookings'));
    }
}
