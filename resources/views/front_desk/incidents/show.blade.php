@extends('layouts.dashboard')

@section('title', 'Report details')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Report details')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $reportType = $report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type;
    $reportTypeLabel = $reportType ? str_replace('_', ' ', ucfirst($reportType)) : 'Report';
    $status = $report->status instanceof \BackedEnum ? $report->status->value : (string) $report->status;
    $statusLabel = str_replace('_', ' ', ucwords($status, '_'));
    $heading = match ($reportType) {
        'broken_amenity' => 'Broken Amenity Report',
        'complaint' => 'Complaint Report',
        'maintenance' => 'Maintenance Report',
        'incident' => 'Incident Report',
        default => 'Guest Report',
    };
    $guestName = $report->guest?->user?->name ?? '—';
    $bookingLabel = $report->booking
        ? ($report->booking->short_number ?? $report->booking->booking_number)
        : null;
    $statusIcon = match ($status) {
        'verified' => 'bi-shield-check',
        'in_progress' => 'bi-arrow-repeat',
        'resolved', 'closed' => 'bi-check-circle',
        'invalid' => 'bi-x-circle',
        default => 'bi-clock',
    };
    $issueTitle = $report->title ?: \Illuminate\Support\Str::limit((string) $report->description, 80);
    $actionLabels = [
        'repair_broken_amenity' => 'Repair broken amenity',
        'assist_guest' => 'Assist guest',
        'contact_maintenance' => 'Contact maintenance',
        'contact_security' => 'Contact security',
        'other' => 'Other',
    ];
    $heroSub = match ($status) {
        'verified' => 'Review the verified report and start resolution with the guest.',
        'in_progress' => 'Confirm the work done and mark this report as resolved.',
        'resolved', 'closed' => 'This report has been resolved. Review the resolution summary below.',
        default => 'Review guest report details and take the next action.',
    };
@endphp

<nav class="rp-report-detail-crumb" aria-label="Breadcrumb">
    <a href="{{ route('front_desk.incidents.index') }}">Reports</a>
    <span aria-hidden="true">/</span>
    <span>Report details</span>
</nav>

<div class="rp-report-detail-hero">
    <div class="rp-report-detail-hero-main">
        <span class="rp-report-detail-hero-icon" aria-hidden="true"><i class="bi bi-wrench-adjustable"></i></span>
        <div>
            <h1 class="rp-report-detail-title">{{ $heading }}</h1>
            <p class="rp-report-detail-sub mb-0">{{ $heroSub }}</p>
        </div>
    </div>
    <span class="rp-report-detail-status is-{{ $status }}">
        <i class="bi {{ $statusIcon }}" aria-hidden="true"></i>
        {{ $statusLabel }}
    </span>
</div>

<div class="rp-report-detail-grid">
    <section class="rp-report-detail-card">
        <h2 class="rp-report-detail-issue">{{ $issueTitle }}</h2>
        <div class="rp-report-detail-tags">
            @if($report->location)
                <span class="rp-report-chip is-location"><i class="bi bi-house-door" aria-hidden="true"></i> {{ $report->location }}</span>
            @endif
            <span class="rp-report-chip is-type"><i class="bi bi-tools" aria-hidden="true"></i> {{ $reportTypeLabel }}</span>
            <span class="rp-report-chip is-status-{{ $status }}"><i class="bi {{ $statusIcon }}" aria-hidden="true"></i> {{ $statusLabel }}</span>
        </div>

        <div class="rp-report-detail-block">
            <div class="rp-report-detail-label">Description</div>
            <p class="rp-report-detail-copy mb-0">{{ $report->description }}</p>
        </div>

        <div class="rp-report-detail-meta">
            <div>
                <div class="rp-report-detail-label">Reported by</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-person" aria-hidden="true"></i> {{ $guestName }}</div>
            </div>
            <div>
                <div class="rp-report-detail-label">Reported</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
            </div>
            @if($report->securityGuard)
                <div>
                    <div class="rp-report-detail-label">Investigated by</div>
                    <div class="rp-report-detail-meta-value"><i class="bi bi-shield-check" aria-hidden="true"></i> {{ $report->securityGuard->name }}</div>
                </div>
            @endif
            @if($bookingLabel)
                <div>
                    <div class="rp-report-detail-label">Booking</div>
                    <div class="rp-report-detail-meta-value"><i class="bi bi-clipboard2" aria-hidden="true"></i> {{ $bookingLabel }}</div>
                </div>
            @endif
        </div>

        @if($report->photo)
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Guest photo</div>
                <div class="rp-report-detail-photo-wrap">
                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpFdGuestPhotoModal" aria-label="View guest photo">
                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                    </button>
                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpFdGuestPhotoModal" title="View full photo" aria-label="View full guest photo">
                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif

        @if($report->investigation_notes)
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Security investigation notes</div>
                <div class="rp-report-note-panel">
                    <p class="rp-report-detail-copy mb-0">{{ $report->investigation_notes }}</p>
                </div>
            </div>
        @endif

        @if($report->investigation_photo)
            <div class="rp-report-detail-block mb-0">
                <div class="rp-report-detail-label">Investigation photo</div>
                <div class="rp-report-detail-photo-wrap">
                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpFdInvestigationPhotoModal" aria-label="View investigation photo">
                        <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo">
                    </button>
                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpFdInvestigationPhotoModal" title="View full photo" aria-label="View full investigation photo">
                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif
    </section>

    <aside class="rp-report-detail-card rp-report-review-card">
        @if($status === 'verified')
            <div class="rp-report-review-head">
                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-play-circle"></i></span>
                <div>
                    <h2 class="rp-report-review-title">Start resolution</h2>
                    <p class="rp-report-review-sub mb-0">Choose an action and note what is being done for the guest.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('front_desk.incidents.progress', $report) }}" class="rp-report-review-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="rpResolutionAction">Resolution action</label>
                    <select id="rpResolutionAction" name="resolution_action" class="form-select" required>
                        @foreach($actionLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('resolution_action', 'repair_broken_amenity') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('resolution_action')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="rpResolutionNotes">Notes</label>
                    <div class="rp-report-notes-wrap">
                        <textarea id="rpResolutionNotes" name="resolution_notes" class="form-control" rows="5" maxlength="1000" placeholder="What's being done to resolve this" required data-rp-notes-count>{{ old('resolution_notes') }}</textarea>
                        <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                    </div>
                    @error('resolution_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit">
                    <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i> Mark In Progress
                </button>
                <p class="rp-report-help mb-0">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    This moves the report into active resolution so staff can finish and close it.
                </p>
            </form>
        @elseif($status === 'in_progress')
            <div class="rp-report-review-head">
                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                <div>
                    <h2 class="rp-report-review-title">Verify &amp; resolve</h2>
                    <p class="rp-report-review-sub mb-0">Confirm the work done and close this report.</p>
                </div>
            </div>

            @if($report->resolution_action)
                <div class="rp-report-detail-block">
                    <div class="rp-report-detail-label">Action in progress</div>
                    <div class="rp-report-detail-meta-value">
                        <i class="bi bi-wrench" aria-hidden="true"></i>
                        {{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('front_desk.incidents.resolve', $report) }}" class="rp-report-review-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="rpResolveNotes">Resolution notes</label>
                    <div class="rp-report-notes-wrap">
                        <textarea id="rpResolveNotes" name="resolution_notes" class="form-control" rows="5" maxlength="1000" placeholder="Confirm what was done to resolve this" required data-rp-notes-count>{{ old('resolution_notes', $report->resolution_notes) }}</textarea>
                        <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                    </div>
                    @error('resolution_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit">
                    <i class="bi bi-check2 me-1" aria-hidden="true"></i> Mark as Resolved
                </button>
                <p class="rp-report-help mb-0">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    The guest will be notified that this report has been resolved.
                </p>
            </form>
        @else
            <div class="rp-report-review-head">
                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-journal-check"></i></span>
                <div>
                    <h2 class="rp-report-review-title">Resolution summary</h2>
                    <p class="rp-report-review-sub mb-0">Final outcome for this guest report.</p>
                </div>
            </div>

            <div class="rp-report-detail-meta">
                @if($report->resolution_action)
                    <div>
                        <div class="rp-report-detail-label">Action taken</div>
                        <div class="rp-report-detail-meta-value">
                            <i class="bi bi-wrench" aria-hidden="true"></i>
                            {{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}
                        </div>
                    </div>
                @endif
                @if($report->resolved_at)
                    <div>
                        <div class="rp-report-detail-label">Resolved</div>
                        <div class="rp-report-detail-meta-value">
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            {{ $report->resolved_at->format('M j, Y · g:i A') }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Resolution notes</div>
                <div class="rp-report-note-panel">
                    <p class="rp-report-detail-copy mb-0">{{ $report->resolution_notes ?? '—' }}</p>
                </div>
            </div>

            <a href="{{ route('front_desk.incidents.index') }}" class="btn btn-rp-soft w-100">Back to reports</a>
        @endif
    </aside>
</div>

@if($report->photo)
<div class="modal fade" id="rpFdGuestPhotoModal" tabindex="-1" aria-labelledby="rpFdGuestPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpFdGuestPhotoModalLabel">Guest photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-photo-modal-img" alt="Guest report photo full size">
            </div>
        </div>
    </div>
</div>
@endif

@if($report->investigation_photo)
<div class="modal fade" id="rpFdInvestigationPhotoModal" tabindex="-1" aria-labelledby="rpFdInvestigationPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpFdInvestigationPhotoModalLabel">Investigation photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-photo-modal-img" alt="Investigation photo full size">
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const notes = document.querySelector('[data-rp-notes-count]');
    const notesValue = document.querySelector('[data-rp-notes-count-value]');
    const syncCount = () => {
        if (!notes || !notesValue) return;
        notesValue.textContent = String((notes.value || '').length);
    };
    notes?.addEventListener('input', syncCount);
    syncCount();
});
</script>
@endpush
