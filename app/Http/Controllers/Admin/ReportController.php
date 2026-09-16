<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Support\ListFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = IncidentReport::query()->with(['guest.user', 'booking'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        ListFilters::applyIncidentSearch($query, $request->input('q'));

        $reports = $query->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function show(IncidentReport $incident): View
    {
        $incident->load(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff', 'attachments']);

        return view('admin.reports.show', ['report' => $incident]);
    }
}
