<?php

namespace App\Http\Controllers\Security;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending' => IncidentReport::query()->where('status', IncidentStatus::Pending)->count(),
            'verified' => IncidentReport::query()->where('status', IncidentStatus::Verified)->count(),
            'invalid' => IncidentReport::query()->where('status', IncidentStatus::Invalid)->count(),
            'in_progress' => IncidentReport::query()->where('status', IncidentStatus::InProgress)->count(),
        ];

        $pendingReports = IncidentReport::query()
            ->with('guest.user')
            ->where('status', IncidentStatus::Pending)
            ->latest()
            ->take(10)
            ->get();

        $chartDays = collect(range(6, 0))->map(fn ($offset) => Carbon::today()->subDays($offset));
        $chartStart = $chartDays->first()->startOfDay();
        $chartEnd = $chartDays->last()->endOfDay();
        $dailyReports = IncidentReport::query()
            ->selectRaw('DATE(created_at) as day, status, COUNT(*) as total')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day', 'status')
            ->get()
            ->groupBy('day');
        $chart = [
            'days' => $chartDays->map(fn ($day) => $day->format('D M j'))->values(),
            'pending' => $chartDays->map(fn ($day) => (int) ($dailyReports[$day->toDateString()] ?? collect())
                ->filter(fn ($report) => $report->status === IncidentStatus::Pending)->sum('total'))->values(),
            'reviewed' => $chartDays->map(fn ($day) => (int) ($dailyReports[$day->toDateString()] ?? collect())
                ->filter(fn ($report) => $report->status !== IncidentStatus::Pending)->sum('total'))->values(),
        ];

        return view('security.dashboard', compact('stats', 'pendingReports', 'chart'));
    }
}
