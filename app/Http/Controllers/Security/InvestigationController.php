<?php

namespace App\Http\Controllers\Security;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\InvestigateIncidentRequest;
use App\Models\IncidentReport;
use App\Services\IncidentReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestigationController extends Controller
{
    public function __construct(protected IncidentReportService $incidentReportService)
    {
    }

    public function index(Request $request): View
    {
        $query = IncidentReport::query()
            ->with(['guest.user', 'booking'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', [IncidentStatus::Pending, IncidentStatus::Verified]);
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('report_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        $reports = $query->paginate(20);

        return view('security.incidents.index', compact('reports'));
    }

    public function show(IncidentReport $incident): View
    {
        $this->authorize('view', $incident);
        $incident->load(['guest.user', 'booking', 'attachments']);

        return view('security.incidents.show', ['report' => $incident]);
    }

    public function investigate(InvestigateIncidentRequest $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('investigate', $incident);

        if ($request->input('action') === 'invalid') {
            $this->incidentReportService->markInvalid(
                $incident,
                $request->user(),
                $request->validated('invalid_reason')
            );

            return back()->with('success', 'Incident marked as invalid.');
        }

        $this->incidentReportService->verify(
            $incident,
            $request->user(),
            [
                'investigation_notes' => $request->input('investigation_notes'),
            ],
            $request->file('investigation_photo')
        );

        return back()->with('success', 'Incident verified.');
    }

    public function verify(Request $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('investigate', $incident);

        $data = $request->validate([
            'investigation_notes' => ['required', 'string', 'max:5000'],
            'investigation_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $this->incidentReportService->verify(
            $incident,
            $request->user(),
            ['investigation_notes' => $data['investigation_notes']],
            $request->file('investigation_photo')
        );

        return back()->with('success', 'Incident verified.');
    }

    public function invalidate(Request $request, IncidentReport $incident): RedirectResponse
    {
        $this->authorize('investigate', $incident);

        $data = $request->validate([
            'invalid_reason' => ['required', 'string', 'max:5000'],
        ]);

        $this->incidentReportService->markInvalid(
            $incident,
            $request->user(),
            $data['invalid_reason']
        );

        return back()->with('success', 'Incident marked as invalid.');
    }
}
