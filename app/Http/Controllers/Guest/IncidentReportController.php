<?php

namespace App\Http\Controllers\Guest;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreIncidentReportRequest;
use App\Models\Booking;
use App\Models\IncidentReport;
use App\Services\IncidentReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentReportController extends Controller
{
    public function __construct(protected IncidentReportService $incidentReportService)
    {
    }

    public function index(Request $request): View
    {
        $query = IncidentReport::query()
            ->where('guest_id', $request->user()->guest?->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('report_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }

        $reports = $query->paginate(5)->withQueryString();

        return view('guest.incidents.index', compact('reports'));
    }

    public function create(Request $request): View
    {
        $bookings = Booking::query()
            ->with('accommodation')
            ->where('guest_id', $request->user()->guest?->id)
            ->whereIn('status', [
                BookingStatus::Approved,
                BookingStatus::CheckedIn,
                BookingStatus::CheckedOut,
            ])
            ->latest()
            ->get();

        return view('guest.incidents.create', compact('bookings'));
    }

    public function store(StoreIncidentReportRequest $request): RedirectResponse
    {
        $report = $this->incidentReportService->submit(
            $request->validated(),
            $request->user(),
            $request->file('photo')
        );

        return redirect()
            ->route('guest.incidents.show', $report)
            ->with('success', 'Incident report submitted.');
    }

    public function show(Request $request, IncidentReport $incident): View
    {
        $this->authorize('view', $incident);
        $incident->load(['booking', 'attachments']);

        return view('guest.incidents.show', ['report' => $incident]);
    }
}
