<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccommodationStatus;
use App\Enums\BookingStatus;
use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\IncidentReport;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_reservations' => Booking::query()->count(),
            'today_reservations' => Booking::query()->whereDate('created_at', today())->count(),
            'total_guests' => Guest::query()->count(),
            'available' => Accommodation::query()->where('status', AccommodationStatus::Available)->count(),
            'occupied' => Accommodation::query()->where('status', AccommodationStatus::Occupied)->count(),
            'total_revenue' => (float) Payment::query()->where('status', 'verified')->sum('amount'),
            'pending_reports' => IncidentReport::query()->where('status', IncidentStatus::Pending)->count(),
            'resolved_reports' => IncidentReport::query()->where('status', IncidentStatus::Resolved)->count(),
            'active_incidents' => IncidentReport::query()
                ->whereIn('status', [IncidentStatus::Pending, IncidentStatus::Verified, IncidentStatus::InProgress])
                ->count(),
        ];

        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->format('M Y'));
        $monthKeys = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->format('Y-m'));

        $reservationMap = Booking::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $revenueMap = Payment::query()
            ->select(DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->where('status', 'verified')
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $charts = [
            'months' => $months->values(),
            'reservations' => $monthKeys->map(fn ($ym) => (int) ($reservationMap[$ym] ?? 0))->values(),
            'revenue' => $monthKeys->map(fn ($ym) => (float) ($revenueMap[$ym] ?? 0))->values(),
            'incident_types' => IncidentReport::query()
                ->select('report_type', DB::raw('COUNT(*) as total'))
                ->groupBy('report_type')
                ->pluck('total', 'report_type')
                ->toArray(),
            'incident_statuses' => IncidentReport::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'occupancy_rate' => $this->occupancyRate(),
        ];

        return view('admin.dashboard', compact('stats', 'charts'));
    }

    protected function occupancyRate(): float
    {
        $total = Accommodation::query()->where('is_active', true)->count();
        if ($total === 0) {
            return 0;
        }

        $occupied = Accommodation::query()->where('status', AccommodationStatus::Occupied)->count();

        return round(($occupied / $total) * 100, 1);
    }
}
