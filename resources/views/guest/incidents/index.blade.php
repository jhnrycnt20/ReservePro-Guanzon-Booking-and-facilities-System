@extends('layouts.public')

@section('title', 'My Reports')

@section('content')
@php
    $statusFilter = request('status');
@endphp
<div class="container rp-public-page-top rp-reservations-index rp-reports-index pb-4">
    <div class="rp-reservations-heading">
        <h1 class="rp-page-intro-title mb-0">My Reports</h1>
        <form method="GET" action="{{ route('guest.incidents.index') }}" class="rp-reservations-filter">
            <label class="visually-hidden" for="reportStatusFilter">Filter reports by status</label>
            <select id="reportStatusFilter" name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All reports</option>
                <option value="pending" @selected($statusFilter === 'pending')>Submitted</option>
                <option value="verified" @selected($statusFilter === 'verified')>Under review</option>
                <option value="in_progress" @selected($statusFilter === 'in_progress')>Being processed</option>
                <option value="resolved" @selected($statusFilter === 'resolved')>Resolved</option>
                <option value="closed" @selected($statusFilter === 'closed')>Closed</option>
                <option value="invalid" @selected($statusFilter === 'invalid')>Invalid</option>
            </select>
            @if(filled(request('q')))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        </form>
    </div>

    <div class="rp-reports-toolbar">
        <button type="button" class="rp-avail-btn-primary rp-avail-btn-primary--inline" data-bs-toggle="modal" data-bs-target="#rpGuestIncidentModal">New Report</button>
    </div>

    <div class="rp-booking-list">
        @forelse($reports as $report)
            @php
                $reportDisplayName = $report->booking?->accommodation?->name ?? $report->title;
            @endphp
            <a href="{{ route('guest.incidents.show', $report) }}" class="rp-booking-list-item">
                <div class="rp-booking-list-media">
                    @if($report->photo)
                        <img src="{{ asset('storage/'.$report->photo) }}" alt="{{ $reportDisplayName }}">
                    @else
                        <div class="rp-booking-list-media-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                    @endif
                </div>
                <div class="rp-booking-list-body">
                    <div class="rp-booking-list-title">{{ $reportDisplayName }}</div>
                    <div class="rp-booking-list-meta">
                        <span class="rp-booking-list-code">{{ $report->report_number }} &middot; {{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }} &middot; {{ $report->location }}</span>
                    </div>
                </div>
                <div class="rp-booking-list-action">
                    <span class="rp-booking-list-view">View Details <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
        @empty
            <div class="rp-booking-empty">
                <i class="bi bi-clipboard2-pulse"></i>
                <p>No reports submitted yet.</p>
                <button type="button" class="rp-avail-btn-primary" data-bs-toggle="modal" data-bs-target="#rpGuestIncidentModal">New Report</button>
            </div>
        @endforelse
    </div>

    @if($reports->total() > 0)
        <p class="rp-reservations-count">Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }} reports</p>
    @endif

    @if($reports->hasPages())
        <div class="rp-simple-pagination mt-4">
            @if($reports->onFirstPage())
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $reports->previousPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-left"></i></a>
            @endif

            @for($page = 1; $page <= $reports->lastPage(); $page++)
                @if($page === $reports->currentPage())
                    <span class="rp-simple-pagination-num is-active">{{ $page }}</span>
                @else
                    <a href="{{ $reports->url($page) }}" class="rp-simple-pagination-num">{{ $page }}</a>
                @endif
            @endfor

            @if($reports->hasMorePages())
                <a href="{{ $reports->nextPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    @endif
</div>
@endsection
