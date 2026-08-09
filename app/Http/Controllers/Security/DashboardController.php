<?php

namespace App\Http\Controllers\Security;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
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

        return view('security.dashboard', compact('stats', 'pendingReports'));
    }
}
