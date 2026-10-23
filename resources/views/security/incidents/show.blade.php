@extends('layouts.dashboard')

@section('title', 'Report details')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Report details')
@section('sidebar')
    @include('partials.sidebar-security')
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
        default => 'Maintenance Report',
    };
    $guestName = $report->guest?->user?->name ?? '—';
    $bookingLabel = $report->booking
        ? ($report->booking->short_number ?? $report->booking->booking_number)
        : null;
    $statusIcon = match ($status) {
        'verified', 'in_progress' => 'bi-check2-circle',
        'resolved', 'closed' => 'bi-check-circle',
        'invalid' => 'bi-x-circle',
        default => 'bi-clock',
    };
    $issueTitle = $report->title ?: \Illuminate\Support\Str::limit((string) $report->description, 80);
@endphp

<nav class="rp-report-detail-crumb" aria-label="Breadcrumb">
    <a href="{{ route('security.incidents.index') }}">Reports</a>
    <span aria-hidden="true">/</span>
    <span>Report details</span>
</nav>

<div class="rp-report-detail-hero">
    <div class="rp-report-detail-hero-main">
        <span class="rp-report-detail-hero-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
        <div>
            <h1 class="rp-report-detail-title">{{ $heading }}</h1>
            <p class="rp-report-detail-sub mb-0">Review and verify guest maintenance reports before forwarding to the front desk.</p>
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
            @if($bookingLabel)
                <div>
                    <div class="rp-report-detail-label">Booking</div>
                    <div class="rp-report-detail-meta-value"><i class="bi bi-clipboard2" aria-hidden="true"></i> {{ $bookingLabel }}</div>
                </div>
            @endif
            <div>
                <div class="rp-report-detail-label">Reported</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
            </div>
        </div>

        @if($report->photo)
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Guest photo</div>
                <div class="rp-report-detail-photo-wrap">
                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpGuestPhotoModal" aria-label="View guest photo">
                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                    </button>
                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpGuestPhotoModal" title="View full photo" aria-label="View full guest photo">
                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif
    </section>

    <aside class="rp-report-detail-card rp-report-review-card">
        @if($status === 'pending')
            <div class="rp-report-review-head">
                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <div>
                    <h2 class="rp-report-review-title">Review &amp; Verification</h2>
                    <p class="rp-report-review-sub mb-0">Check the details and verify the report status.</p>
                </div>
            </div>

            <div class="rp-report-action-toggle" role="tablist" aria-label="Review action">
                <button type="button" class="rp-report-action-btn is-active" id="rp-verify-tab" data-bs-toggle="tab" data-bs-target="#rp-verify-pane" role="tab" aria-controls="rp-verify-pane" aria-selected="true">
                    <i class="bi bi-check2" aria-hidden="true"></i> Verify report
                </button>
                <button type="button" class="rp-report-action-btn" id="rp-invalid-tab" data-bs-toggle="tab" data-bs-target="#rp-invalid-pane" role="tab" aria-controls="rp-invalid-pane" aria-selected="false">
                    <i class="bi bi-x-lg" aria-hidden="true"></i> Mark invalid
                </button>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="rp-verify-pane" role="tabpanel" aria-labelledby="rp-verify-tab">
                    <form method="POST" action="{{ route('security.incidents.verify', $report) }}" enctype="multipart/form-data" class="rp-report-review-form" data-rp-report-review>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="rpInvestigationNotes">Investigation notes / findings</label>
                            <div class="rp-report-notes-wrap">
                                <textarea id="rpInvestigationNotes" name="investigation_notes" class="form-control" rows="5" maxlength="1000" placeholder="What did you find on site?" required data-rp-notes-count>{{ old('investigation_notes') }}</textarea>
                                <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                            </div>
                            @error('investigation_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo (optional)</label>
                            <label class="rp-report-upload">
                                <input type="file" name="investigation_photo" accept="image/jpeg,image/png,image/webp" class="rp-report-upload-input">
                                <span class="rp-report-upload-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                                <span class="rp-report-upload-title" data-rp-upload-label>Choose File or drag and drop</span>
                                <span class="rp-report-upload-hint">JPG, PNG (Max 5MB)</span>
                            </label>
                            @error('investigation_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit">
                            <i class="bi bi-check2 me-1" aria-hidden="true"></i> Verify &amp; Forward to Front Desk
                        </button>
                        <p class="rp-report-help mb-0">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            This will mark the report as verified and send it to the front desk for further action.
                        </p>
                    </form>
                </div>
                <div class="tab-pane fade" id="rp-invalid-pane" role="tabpanel" aria-labelledby="rp-invalid-tab">
                    <form method="POST" action="{{ route('security.incidents.invalidate', $report) }}" class="rp-report-review-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="rpInvalidReason">Reason</label>
                            <textarea id="rpInvalidReason" name="invalid_reason" class="form-control" rows="5" maxlength="1000" placeholder="Why isn't this a valid report?" required>{{ old('invalid_reason') }}</textarea>
                            @error('invalid_reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-lg me-1" aria-hidden="true"></i> Mark Invalid &amp; Close
                        </button>
                        <p class="rp-report-help mb-0">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            The guest will be notified that this report was marked invalid.
                        </p>
                    </form>
                </div>
            </div>
        @else
            <div class="rp-report-review-head">
                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <div>
                    <h2 class="rp-report-review-title">Investigation result</h2>
                    <p class="rp-report-review-sub mb-0">This report has already been reviewed.</p>
                </div>
            </div>
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">{{ $status === 'invalid' ? 'Invalid reason' : 'Investigation notes' }}</div>
                <p class="rp-report-detail-copy mb-0">{{ $report->investigation_notes ?? $report->invalid_reason ?? '—' }}</p>
            </div>
            @if($report->investigation_photo)
                <div class="rp-report-detail-block">
                    <div class="rp-report-detail-label">Investigation photo</div>
                    <div class="rp-report-detail-photo-wrap">
                        <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpInvestigationPhotoModal" aria-label="View investigation photo">
                            <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo">
                        </button>
                        <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpInvestigationPhotoModal" title="View full photo" aria-label="View full investigation photo">
                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            @endif
            <a href="{{ route('security.incidents.index') }}" class="btn btn-rp-soft w-100 mt-2">Back to reports</a>
        @endif
    </aside>
</div>

@if($report->photo)
<div class="modal fade" id="rpGuestPhotoModal" tabindex="-1" aria-labelledby="rpGuestPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpGuestPhotoModalLabel">Guest photo</h2>
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
<div class="modal fade" id="rpInvestigationPhotoModal" tabindex="-1" aria-labelledby="rpInvestigationPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpInvestigationPhotoModalLabel">Investigation photo</h2>
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
    const toggle = document.querySelector('.rp-report-action-toggle');
    toggle?.querySelectorAll('[data-bs-toggle="tab"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', () => {
            toggle.querySelectorAll('.rp-report-action-btn').forEach((el) => el.classList.remove('is-active'));
            btn.classList.add('is-active');
        });
    });

    const notes = document.querySelector('[data-rp-notes-count]');
    const notesValue = document.querySelector('[data-rp-notes-count-value]');
    const syncCount = () => {
        if (!notes || !notesValue) return;
        notesValue.textContent = String((notes.value || '').length);
    };
    notes?.addEventListener('input', syncCount);
    syncCount();

    const upload = document.querySelector('.rp-report-upload-input');
    const uploadLabel = document.querySelector('[data-rp-upload-label]');
    upload?.addEventListener('change', () => {
        if (!uploadLabel) return;
        uploadLabel.textContent = upload.files?.[0]?.name || 'Choose File or drag and drop';
    });
});
</script>
@endpush
