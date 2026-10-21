<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Services\FrontDeskAutomationService;
use App\Models\Accommodation;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\IncidentReport;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected FrontDeskAutomationService $automation)
    {
    }

    public function index(): View
    {
        $this->automation->runDueActions(auth()->user());

        $stats = [
            'today_checkins' => Booking::query()
                ->whereDate('check_in_date', today())
                ->whereIn('status', [BookingStatus::Approved, BookingStatus::CheckedIn])
                ->count(),
            'today_checkouts' => Booking::query()
                ->whereDate('check_out_date', today())
                ->where('status', BookingStatus::CheckedIn)
                ->count(),
            'reserved' => Booking::query()->awaitingDeposit()->count(),
            'booked' => Booking::query()
                ->onReservationQueue()
                ->depositMet()
                ->where('remaining_balance', '>', 0)
                ->count(),
            'ready_checkin' => Booking::query()
                ->where('status', BookingStatus::Approved)
                ->fullyPaid()
                ->count(),
            'occupied' => Accommodation::query()->where('status', AccommodationStatus::Occupied)->count(),
            'available' => Accommodation::query()->where('status', AccommodationStatus::Available)->count(),
            'pending_incidents' => IncidentReport::query()
                ->whereIn('status', [IncidentStatus::Verified, IncidentStatus::InProgress])
                ->count(),
        ];

        $pendingReservations = Booking::query()
            ->with(['guest.user', 'accommodation'])
            ->frontDeskReservations()
            ->latest()
            ->take(10)
            ->get();

        $recentActivities = AuditLog::query()->latest()->take(8)->get();

        $chartDays = collect(range(6, 0))->map(fn ($offset) => Carbon::today()->subDays($offset));
        $chartStart = $chartDays->first()->toDateString();
        $chartEnd = $chartDays->last()->toDateString();
        $checkinCounts = Booking::query()
            ->selectRaw('DATE(check_in_date) as day, COUNT(*) as total')
            ->whereBetween('check_in_date', [$chartStart, $chartEnd])
            ->whereIn('status', [BookingStatus::Approved, BookingStatus::CheckedIn])
            ->groupBy('day')
            ->pluck('total', 'day');
        $checkoutCounts = Booking::query()
            ->selectRaw('DATE(check_out_date) as day, COUNT(*) as total')
            ->whereBetween('check_out_date', [$chartStart, $chartEnd])
            ->whereIn('status', [BookingStatus::Approved, BookingStatus::CheckedIn])
            ->groupBy('day')
            ->pluck('total', 'day');
        $chart = [
            'days' => $chartDays->map(fn ($day) => $day->format('D M j'))->values(),
            'checkins' => $chartDays->map(fn ($day) => (int) ($checkinCounts[$day->toDateString()] ?? 0))->values(),
            'checkouts' => $chartDays->map(fn ($day) => (int) ($checkoutCounts[$day->toDateString()] ?? 0))->values(),
        ];

        return view('front_desk.dashboard', compact('stats', 'pendingReservations', 'recentActivities', 'chart'));
    }
}
