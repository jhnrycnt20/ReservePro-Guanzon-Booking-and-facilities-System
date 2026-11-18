<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\ResolveIncidentRequest;
use App\Models\IncidentReport;
use App\Services\IncidentReportService;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentResolutionController extends Controller
{
    public function __construct(protected IncidentReportService $incidentReportService)
    {
    }

    public function index(Request $request): View
    {
        $query = IncidentReport::query()
            ->with(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default: unresolved queue (still needs staff action).
            $query->whereIn('status', [
                IncidentStatus::Verified,
                IncidentStatus::InProgress,
            ]);
        }

        ListFilters::applyIncidentSearch($query, $request->input('q'));

        $reports = $query->paginate(20)->appends($request->except('report'));
        $modalReports = collect($reports->items());
        $selectedReportId = $request->integer('report') ?: (int) $request->old('_modal');

        if ($selectedReportId && ! $modalReports->contains('id', $selectedReportId)) {
            $selectedReport = IncidentReport::query()
                ->with(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff'])
                ->find($selectedReportId);

            if ($selectedReport) {
                $modalReports->push($selectedReport);
            }
        }

        $unresolvedCount = IncidentReport::query()
            ->whereIn('status', [
                IncidentStatus::Verified,
                IncidentStatus::InProgress,
            ])
            ->count();

        return view('front_desk.incidents.index', compact(
            'reports',
            'modalReports',
            'selectedReportId',
            'unresolvedCount'
        ));
    }

    public function show(IncidentReport $incident): RedirectResponse
    {
        return redirect()->route('front_desk.incidents.index', ['report' => $incident->id]);
    }

    public function progress(Request $request, IncidentReport $incident): RedirectResponse
    {
        return $this->startProgress($request, $incident);
    }

    public function startProgress(Request $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('resolve', $incident);

        $data = $request->validate([
            'resolution_action' => ['required', 'string', 'max:255'],
            'resolution_notes' => ['required', 'string', 'max:5000'],
        ]);

        $this->incidentReportService->startProgress($incident, $request->user(), $data);

        return back()->with('success', 'Incident marked in progress.');
    }

    public function resolve(ResolveIncidentRequest $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('resolve', $incident);

        $this->incidentReportService->resolve(
            $incident,
            $request->user(),
            $request->validated('resolution_notes')
        );

        return back()->with('success', 'Incident resolved.');
    }

    public function close(Request $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('resolve', $incident);
        $this->incidentReportService->close($incident, $request->user());

        return back()->with('success', 'Incident closed.');
    }
}
