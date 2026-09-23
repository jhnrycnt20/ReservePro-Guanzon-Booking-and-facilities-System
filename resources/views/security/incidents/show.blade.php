@extends('layouts.dashboard')

@section('title', 'Investigate Report')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Investigate '.$report->report_number)
@section('sidebar')
    @include('partials.sidebar-security')
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
                    <span>&middot;</span>
                    <x-status-badge :status="$report->status" plain />
                </div>
            </div>
            <div class="text-muted small mt-3 mb-1">Description</div>
            <p class="mb-4">{{ $report->description }}</p>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="text-muted small">Reported by</div>
                    <div>{{ $report->guest?->user?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Reported</div>
                    <div>{{ $report->created_at?->format('M d, Y g:i A') ?? '—' }}</div>
                </div>
                @if($report->booking)
                    <div class="col-md-6">
                        <div class="text-muted small">Booking</div>
                        <div>{{ $report->booking->short_number ?? $report->booking->booking_number }}</div>
                    </div>
                @endif
            </div>

            @if($report->photo)
                <div class="text-muted small mb-1">Guest photo</div>
                <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded" alt="Report photo">
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        @if($status === 'pending')
            <div class="rp-card">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="rp-verify-tab" data-bs-toggle="tab" data-bs-target="#rp-verify-pane" type="button" role="tab" aria-controls="rp-verify-pane" aria-selected="true">Verify</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rp-invalid-tab" data-bs-toggle="tab" data-bs-target="#rp-invalid-pane" type="button" role="tab" aria-controls="rp-invalid-pane" aria-selected="false">Mark invalid</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="rp-verify-pane" role="tabpanel" aria-labelledby="rp-verify-tab">
                        <form method="POST" action="{{ route('security.incidents.verify', $report) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Investigation notes / findings</label>
                                <textarea name="investigation_notes" class="form-control" rows="4" placeholder="What did you find on site?" required>{{ old('investigation_notes') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Photo (optional)</label>
                                <input type="file" name="investigation_photo" class="form-control" accept="image/*">
                            </div>
                            <button class="btn btn-success w-100">Verify &amp; Forward to Front Desk</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="rp-invalid-pane" role="tabpanel" aria-labelledby="rp-invalid-tab">
                        <form method="POST" action="{{ route('security.incidents.invalidate', $report) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <textarea name="invalid_reason" class="form-control" rows="4" placeholder="Why isn't this a valid report?" required>{{ old('invalid_reason') }}</textarea>
                            </div>
                            <button class="btn btn-outline-danger w-100">Mark Invalid &amp; Close</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="rp-card">
                <h3 class="h6 mb-3">Investigation notes</h3>
                <p class="mb-0">{{ $report->investigation_notes ?? $report->invalid_reason ?? '—' }}</p>
                @if($report->investigation_photo)
                    <div class="text-muted small mt-3 mb-1">Investigation photo</div>
                    <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="img-fluid rounded" alt="Investigation photo">
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
