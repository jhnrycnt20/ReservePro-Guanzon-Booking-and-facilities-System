@extends('layouts.public')

@section('title', 'My Reservations')

@section('content')
<div class="container rp-public-page-top rp-public-page-top--tight pb-4">
    <a href="{{ route('guest.dashboard') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">My Reservations</h1>
    </div>

    <div class="rp-booking-list">
        @forelse($bookings as $booking)
            <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-booking-list-item">
                <div class="rp-booking-list-media">
                    <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}">
                </div>
                <div class="rp-booking-list-body">
                    <div class="rp-booking-list-top">
                        <div>
                            <div class="rp-booking-list-title">{{ $booking->accommodation->name }}</div>
                            <div class="rp-booking-list-dates">
                                <div class="rp-booking-list-date-block">
                                    <span class="rp-booking-list-date-label">Check-in</span>
                                    <span class="rp-booking-list-date-value">{{ $booking->check_in_date->format('M d, Y') }}</span>
                                </div>
                                <div class="rp-booking-list-date-block">
                                    <span class="rp-booking-list-date-label">Check-out</span>
                                    <span class="rp-booking-list-date-value">{{ $booking->check_out_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rp-booking-list-arrow"><i class="bi bi-chevron-right"></i></div>
            </a>
        @empty
            <div class="rp-booking-empty">
                <i class="bi bi-calendar-x"></i>
                <p>You don't have any reservations yet.</p>
                <a href="{{ route('accommodations.browse') }}" class="rp-avail-btn-primary">Browse Accommodations</a>
            </div>
        @endforelse
    </div>

    @if($bookings->hasPages())
        <div class="rp-simple-pagination mt-4">
            @if($bookings->onFirstPage())
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $bookings->previousPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-left"></i></a>
            @endif

            @for($page = 1; $page <= $bookings->lastPage(); $page++)
                @if($page === $bookings->currentPage())
                    <span class="rp-simple-pagination-num is-active">{{ $page }}</span>
                @else
                    <a href="{{ $bookings->url($page) }}" class="rp-simple-pagination-num">{{ $page }}</a>
                @endif
            @endfor

            @if($bookings->hasMorePages())
                <a href="{{ $bookings->nextPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    @endif
</div>
@endsection
