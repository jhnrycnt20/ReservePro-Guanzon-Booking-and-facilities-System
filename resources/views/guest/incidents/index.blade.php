@extends('layouts.public')

@section('title', 'My Reports')

@section('content')
<div class="container rp-public-page-top rp-public-page-top--tight pb-4">
    <div class="rp-page-intro d-flex align-items-end justify-content-between flex-wrap gap-3">
        <h1 class="rp-page-intro-title mb-0">My Reports</h1>
        <a href="{{ route('guest.incidents.create') }}" class="rp-avail-btn-primary rp-avail-btn-primary--inline">New Report</a>
    </div>

    <div class="rp-booking-list">
        @forelse($reports as $report)
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
                            <div class="rp-booking-list-dates">{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }} &middot; {{ $report->location }}</div>
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
