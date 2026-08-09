<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\IncidentReport;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'today_checkins' => Booking::query()
                ->whereDate('check_in_date', today())
                ->whereIn('status', [BookingStatus::Approved, BookingStatus::CheckedIn])
                ->count(),
            'today_checkouts' => Booking::query()
                ->whereDate('check_out_date', today())
                ->where('status', BookingStatus::CheckedIn)
                ->count(),
            'pending' => Booking::query()->where('status', BookingStatus::Pending)->count(),
            'approved' => Booking::query()->where('status', BookingStatus::Approved)->count(),
            'pending_payments' => Payment::query()->where('status', 'pending')->count(),
            'occupied' => Accommodation::query()->where('status', AccommodationStatus::Occupied)->count(),
            'available' => Accommodation::query()->where('status', AccommodationStatus::Available)->count(),
            'pending_incidents' => IncidentReport::query()
                ->whereIn('status', [IncidentStatus::Verified, IncidentStatus::InProgress])
                ->count(),
        ];

        $pendingReservations = Booking::query()
            ->with(['guest.user', 'accommodation'])
            ->where('status', BookingStatus::Pending)
            ->latest()
            ->take(10)
            ->get();

        $recentActivities = AuditLog::query()->latest()->take(8)->get();

        return view('front_desk.dashboard', compact('stats', 'pendingReservations', 'recentActivities'));
    }
}
