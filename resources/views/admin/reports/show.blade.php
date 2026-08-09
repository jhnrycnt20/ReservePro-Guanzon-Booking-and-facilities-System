@extends('layouts.dashboard')

@section('title', 'Report Details')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Report '.$report->report_number)
@section('page_subtitle', 'Read-only incident monitoring')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card mb-3">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ $report->title }}</h2>
                    <div class="text-muted">
                        {{ $report->location }} ·
                        {{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}
                    </div>
                </div>
                <x-status-badge :status="$report->status" />
            </div>
            <p>{{ $report->description }}</p>
            @if($report->photo)
                <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded" alt="Report photo">
            @endif
        </div>
        <div class="rp-card">
            <h3 class="h6">Investigation</h3>
            <p class="mb-3">{{ $report->investigation_notes ?? '—' }}</p>
            <h3 class="h6">Resolution</h3>
            <p class="mb-0">{{ $report->resolution_notes ?? '—' }}</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-card">
            <div class="text-muted small">Guest</div>
            <div class="mb-3">{{ $report->guest?->user?->name ?? '—' }}</div>
            <div class="text-muted small">Booking</div>
            <div class="mb-3">{{ $report->booking->booking_number ?? '—' }}</div>
            <div class="text-muted small">Security</div>
            <div class="mb-3">{{ $report->securityGuard->name ?? '—' }}</div>
            <div class="text-muted small">Front desk</div>
            <div class="mb-3">{{ $report->frontDeskStaff->name ?? '—' }}</div>
            <div class="text-muted small">Submitted</div>
            <div class="mb-3">{{ $report->created_at?->format('M d, Y g:i A') }}</div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-rp-soft w-100">Back to list</a>
        </div>
    </div>
</div>
@endsection
