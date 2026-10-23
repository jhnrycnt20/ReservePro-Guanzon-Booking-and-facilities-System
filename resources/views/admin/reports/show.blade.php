@extends('layouts.dashboard')

@section('title', 'Report details')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Report details')
@section('sidebar')
    @include('partials.sidebar-admin')
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
@endphp

<nav class="rp-report-detail-crumb" aria-label="Breadcrumb">
    <a href="{{ route('admin.reports.index') }}">Reports</a>
    <span aria-hidden="true">/</span>
    <span>Report details</span>
</nav>

<div class="rp-report-detail-hero">
    <div class="rp-report-detail-hero-main">
        <span class="rp-report-detail-hero-icon" aria-hidden="true"><i class="bi bi-clipboard2-pulse"></i></span>
        <div>
            <h1 class="rp-report-detail-title">{{ $heading }}</h1>
            <p class="rp-report-detail-sub mb-0">Full record of the guest report, investigation, and resolution.</p>
        </div>
    </div>
    <span class="rp-report-detail-status is-{{ $status }}">
        <i class="bi {{ $statusIcon }}" aria-hidden="true"></i>
        {{ $statusLabel }}
    </span>
</div>

<div class="rp-report-detail-grid">
    <section class="rp-report-detail-card">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
            <div>
                <div class="rp-report-detail-label mb-1">Report #</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-hash" aria-hidden="true"></i> {{ $report->report_number }}</div>
            </div>
        </div>

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

        @if($report->photo)
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Guest photo</div>
                <div class="rp-report-detail-photo-wrap">
                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpAdminGuestPhotoModal" aria-label="View guest photo">
                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                    </button>
                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpAdminGuestPhotoModal" title="View full photo" aria-label="View full guest photo">
                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endif

        @if($report->investigation_notes || $report->investigation_photo)
            <div class="rp-report-detail-block">
                <div class="rp-report-detail-label">Security investigation</div>
                <div class="rp-report-note-panel">
                    <p class="rp-report-detail-copy mb-0">{{ $report->investigation_notes ?? '—' }}</p>
                </div>
                @if($report->investigation_photo)
                    <div class="rp-report-detail-photo-wrap mt-3">
                        <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpAdminInvestigationPhotoModal" aria-label="View investigation photo">
                            <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo">
                        </button>
                        <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpAdminInvestigationPhotoModal" title="View full photo" aria-label="View full investigation photo">
                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        @if($report->resolution_notes || $report->resolution_action || $report->invalid_reason)
            <div class="rp-report-detail-block mb-0">
                <div class="rp-report-detail-label">{{ $status === 'invalid' ? 'Invalid reason' : 'Resolution' }}</div>
                @if($report->resolution_action && $status !== 'invalid')
                    <div class="rp-report-detail-meta-value mb-2">
                        <i class="bi bi-wrench" aria-hidden="true"></i>
                        {{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}
                    </div>
                @endif
                <div class="rp-report-note-panel">
                    <p class="rp-report-detail-copy mb-0">
                        {{ $status === 'invalid' ? ($report->invalid_reason ?? '—') : ($report->resolution_notes ?? '—') }}
                    </p>
                </div>
            </div>
        @endif
    </section>

    <aside class="rp-report-detail-card rp-report-review-card">
        <div class="rp-report-review-head">
            <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
            <div>
                <h2 class="rp-report-review-title">People &amp; timing</h2>
                <p class="rp-report-review-sub mb-0">Who handled this report and when it moved.</p>
            </div>
        </div>

        <div class="rp-report-detail-meta" style="grid-template-columns: 1fr;">
            <div>
                <div class="rp-report-detail-label">Guest</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-person" aria-hidden="true"></i> {{ $guestName }}</div>
            </div>
            @if($bookingLabel)
                <div>
                    <div class="rp-report-detail-label">Booking</div>
                    <div class="rp-report-detail-meta-value"><i class="bi bi-clipboard2" aria-hidden="true"></i> {{ $bookingLabel }}</div>
                </div>
            @endif
            <div>
                <div class="rp-report-detail-label">Security</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-shield-check" aria-hidden="true"></i> {{ $report->securityGuard->name ?? '—' }}</div>
            </div>
            <div>
                <div class="rp-report-detail-label">Front desk</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $report->frontDeskStaff->name ?? '—' }}</div>
            </div>
            <div>
                <div class="rp-report-detail-label">Submitted</div>
                <div class="rp-report-detail-meta-value"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
            </div>
            @if($report->resolved_at)
                <div>
                    <div class="rp-report-detail-label">Resolved</div>
                    <div class="rp-report-detail-meta-value"><i class="bi bi-check2-circle" aria-hidden="true"></i> {{ $report->resolved_at->format('M j, Y · g:i A') }}</div>
                </div>
            @endif
        </div>

        <a href="{{ route('admin.reports.index') }}" class="btn btn-rp-soft w-100 mt-2">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to reports
        </a>
    </aside>
</div>

@if($report->photo)
<div class="modal fade" id="rpAdminGuestPhotoModal" tabindex="-1" aria-labelledby="rpAdminGuestPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpAdminGuestPhotoModalLabel">Guest photo</h2>
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
<div class="modal fade" id="rpAdminInvestigationPhotoModal" tabindex="-1" aria-labelledby="rpAdminInvestigationPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpAdminInvestigationPhotoModalLabel">Investigation photo</h2>
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
