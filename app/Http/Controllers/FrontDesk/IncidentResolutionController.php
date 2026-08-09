<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\ResolveIncidentRequest;
use App\Models\IncidentReport;
use App\Services\IncidentReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentResolutionController extends Controller
{
    public function __construct(protected IncidentReportService $incidentReportService)
    {
    }

    public function index(): View
    {
        $reports = IncidentReport::query()
            ->with(['guest.user', 'booking'])
            ->whereIn('status', [
                IncidentStatus::Verified,
                IncidentStatus::InProgress,
                IncidentStatus::Resolved,
            ])
            ->latest()
            ->paginate(20);

        return view('front_desk.incidents.index', compact('reports'));
    }

    public function show(IncidentReport $incident): View
    {
        $incident->load(['guest.user', 'booking', 'attachments', 'securityGuard']);

        return view('front_desk.incidents.show', ['report' => $incident]);
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
