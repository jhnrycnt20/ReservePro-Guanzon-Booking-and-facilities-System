<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Support\ListFilters;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = IncidentReport::query()->with(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        ListFilters::applyIncidentSearch($query, $request->input('q'));

        $reports = $query->paginate(20)->appends($request->except('report'));
        $modalReports = collect($reports->items());
        $selectedReportId = $request->integer('report');

        if ($selectedReportId && ! $modalReports->contains('id', $selectedReportId)) {
            $selectedReport = IncidentReport::query()
                ->with(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff'])
                ->find($selectedReportId);

            if ($selectedReport) {
                $modalReports->push($selectedReport);
            }
        }

        return view('admin.reports.index', compact('reports', 'modalReports', 'selectedReportId'));
    }

    public function show(IncidentReport $incident): RedirectResponse
    {
        return redirect()->route('admin.reports.index', ['report' => $incident->id]);
    }
}
