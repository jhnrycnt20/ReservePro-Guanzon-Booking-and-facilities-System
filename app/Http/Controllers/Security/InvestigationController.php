<?php

namespace App\Http\Controllers\Security;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\InvestigateIncidentRequest;
use App\Models\IncidentReport;
use App\Services\IncidentReportService;
use App\Support\ListFilters;
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
        $toRules = ['nullable', 'date'];
        if ($request->filled('from')) {
            $toRules[] = 'after_or_equal:from';
        }
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => $toRules,
        ]);

        $query = IncidentReport::query()
            ->with(['guest.user', 'booking', 'securityGuard', 'frontDeskStaff'])
            ->latest();

        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', [IncidentStatus::Pending, IncidentStatus::Verified]);
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

        return view('security.incidents.index', compact('reports', 'modalReports', 'selectedReportId'));
    }

    public function show(IncidentReport $incident): RedirectResponse
    {
        $this->authorize('view', $incident);

        return redirect()->route('security.incidents.index', ['report' => $incident->id]);
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
            'investigation_notes' => ['required', 'string', 'max:1000'],
            'investigation_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
