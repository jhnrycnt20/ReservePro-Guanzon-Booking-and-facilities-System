@extends('layouts.dashboard')

@section('title', 'Resolve Report')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Resolve '.$report->report_number)
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $reportType = $report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type;
    $reportTypeLabel = $reportType ? str_replace('_', ' ', ucfirst($reportType)) : null;
    $status = $report->status instanceof \BackedEnum ? $report->status->value : $report->status;
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card">
            <div class="mb-1">
                <h2 class="h5 mb-1">{{ $report->title }}</h2>
                <div class="text-muted small d-flex align-items-center gap-2">
                    <span>{{ $report->location }}@if($reportTypeLabel) &middot; {{ $reportTypeLabel }}@endif</span>
                    <x-status-badge :status="$report->status" plain />
                </div>
            </div>
            <p class="mt-3 mb-4">{{ $report->description }}</p>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="text-muted small">Reported by</div>
                    <div>{{ $report->guest?->user?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Reported</div>
                    <div>{{ $report->created_at?->format('M d, Y g:i A') ?? '—' }}</div>
                </div>
                @if($report->securityGuard)
                    <div class="col-md-6">
                        <div class="text-muted small">Investigated by</div>
                        <div>{{ $report->securityGuard->name }}</div>
                    </div>
                @endif
                @if($report->booking)
                    <div class="col-md-6">
                        <div class="text-muted small">Booking</div>
                        <div>{{ $report->booking->short_number ?? $report->booking->booking_number }}</div>
                    </div>
                @endif
            </div>

            @if($report->photo)
                <div class="text-muted small mb-1">Guest photo</div>
                <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded mb-4" alt="Report photo">
            @endif

            @if($report->investigation_notes)
                <div class="rp-note-block mb-3">
                    <div class="text-muted small mb-1">Security investigation notes</div>
                    <p class="mb-0">{{ $report->investigation_notes }}</p>
                </div>
            @endif

            @if($report->investigation_photo)
                <div class="text-muted small mb-1">Investigation photo</div>
                <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="img-fluid rounded" alt="Investigation photo">
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        @if($status === 'verified')
            <div class="rp-card">
                <h3 class="h6 mb-3">Start resolution</h3>
                <form method="POST" action="{{ route('front_desk.incidents.progress', $report) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Resolution action</label>
                        <select name="resolution_action" class="form-select" required>
                            <option value="repair_broken_amenity">Repair Broken Amenity</option>
                            <option value="assist_guest">Assist Guest</option>
                            <option value="contact_maintenance">Contact Maintenance</option>
                            <option value="contact_security">Contact Security</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="4" placeholder="What's being done to resolve this" required>{{ old('resolution_notes') }}</textarea>
                    </div>
                    <button class="btn btn-rp-primary w-100">Mark In Progress</button>
                </form>
            </div>
        @elseif($status === 'in_progress')
            <div class="rp-card">
                <h3 class="h6 mb-3">Verify &amp; resolve</h3>
                <form method="POST" action="{{ route('front_desk.incidents.resolve', $report) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Resolution notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="4" placeholder="Confirm what was done to resolve this" required>{{ old('resolution_notes', $report->resolution_notes) }}</textarea>
                    </div>
                    <button class="btn btn-rp-primary w-100">Mark as Resolved</button>
                </form>
            </div>
        @else
            <div class="rp-card">
                <h3 class="h6 mb-3">Resolution summary</h3>
                @if($report->resolution_action || $report->resolved_at)
                    <div class="row g-3 mb-3">
                        @if($report->resolution_action)
                            <div class="col-6">
                                <div class="text-muted small">Action taken</div>
                                <div>{{ str_replace('_', ' ', ucfirst($report->resolution_action)) }}</div>
                            </div>
                        @endif
                        @if($report->resolved_at)
                            <div class="col-6">
                                <div class="text-muted small">Resolved</div>
                                <div>{{ $report->resolved_at->format('M d, Y g:i A') }}</div>
                            </div>
                        @endif
                    </div>
                @endif
                <div class="text-muted small mb-1">Resolution notes</div>
                <p class="mb-0">{{ $report->resolution_notes ?? '—' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
