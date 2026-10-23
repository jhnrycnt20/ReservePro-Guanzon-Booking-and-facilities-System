@extends('layouts.public')

@section('title', 'My Reports')

@section('content')
@php
    $statusFilter = request('status');
@endphp
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reservations</a>

    <div class="rp-page-intro d-flex align-items-end justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="rp-page-intro-title mb-1">My Reports</h1>
            <p class="rp-page-intro-copy mb-0">Track whether your report is still being processed or already resolved.</p>
        </div>
        <a href="{{ route('guest.incidents.create') }}" class="rp-avail-btn-primary rp-avail-btn-primary--inline">New Report</a>
    </div>

    <div class="rp-report-quick-filters mb-3">
        <a href="{{ route('guest.incidents.index') }}" class="rp-report-quick-filter {{ blank($statusFilter) ? 'is-active' : '' }}">All</a>
        <a href="{{ route('guest.incidents.index', ['status' => 'pending']) }}" class="rp-report-quick-filter {{ $statusFilter === 'pending' ? 'is-active' : '' }}">Submitted</a>
        <a href="{{ route('guest.incidents.index', ['status' => 'in_progress']) }}" class="rp-report-quick-filter {{ $statusFilter === 'in_progress' ? 'is-active' : '' }}">Being processed</a>
        <a href="{{ route('guest.incidents.index', ['status' => 'resolved']) }}" class="rp-report-quick-filter {{ $statusFilter === 'resolved' ? 'is-active' : '' }}">Resolved</a>
    </div>

    @include('partials.list-filters', [
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Status',
                'empty' => 'All statuses',
                'value' => $statusFilter,
                'options' => [
                    'pending' => 'Submitted',
                    'verified' => 'Under review',
                    'in_progress' => 'Being processed',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                    'invalid' => 'Invalid',
                ],
            ],
        ],
        'searchPlaceholder' => 'Report #, title, or location',
        'clearUrl' => route('guest.incidents.index'),
    ])

    <div class="rp-booking-list">
        @forelse($reports as $report)
            @php
                $statusValue = $report->status instanceof \BackedEnum ? $report->status->value : (string) $report->status;
                $progressLabel = match ($statusValue) {
                    'pending' => 'Submitted — waiting for review',
                    'verified' => 'Under review',
                    'in_progress' => 'Being processed',
                    'resolved', 'closed' => 'Resolved',
                    'invalid' => 'Invalid',
                    default => str_replace('_', ' ', ucwords($statusValue, '_')),
                };
            @endphp
            <a href="{{ route('guest.incidents.show', $report) }}" class="rp-booking-list-item">
                <div class="rp-booking-list-media">
                    @if($report->photo)
                        <img src="{{ asset('storage/'.$report->photo) }}" alt="{{ $report->title }}">
                    @else
                        <div class="rp-booking-list-media-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                    @endif
                </div>
                <div class="rp-booking-list-body">
                    <div class="rp-booking-list-top">
                        <div>
                            <div class="rp-booking-list-title">{{ $report->title }}</div>
                            <div class="rp-booking-list-dates">
                                {{ $report->report_number }}
                                &middot; {{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}
                                &middot; {{ $report->location }}
                            </div>
                            <div class="rp-report-progress-inline">{{ $progressLabel }}</div>
                        </div>
                        <x-status-badge :status="$report->status" />
                    </div>
                </div>
                <div class="rp-booking-list-arrow"><i class="bi bi-chevron-right"></i></div>
            </a>
        @empty
            <div class="rp-booking-empty">
                <i class="bi bi-clipboard2-pulse"></i>
                <p>No reports submitted yet.</p>
                <a href="{{ route('guest.incidents.create') }}" class="rp-avail-btn-primary">New Report</a>
            </div>
        @endforelse
    </div>

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
