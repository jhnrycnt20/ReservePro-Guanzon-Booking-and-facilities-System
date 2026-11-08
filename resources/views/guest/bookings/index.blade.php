@extends('layouts.public')

@section('title', 'My Reservations')

@section('content')
@php
    $hasBookings = $bookings->total() > 0;
    $hasFilter = filled(request('payment_status'));
@endphp
<div class="container rp-public-page-top rp-reservations-index pb-4">
    @if(session('error'))
        <div class="alert alert-warning border-0 shadow-sm mb-3">{{ session('error') }}</div>
    @endif
    <div class="rp-reservations-heading">
        <h1 class="rp-page-intro-title mb-0">My Reservations</h1>
        @if($hasBookings || $hasFilter)
            <form method="GET" action="{{ route('guest.bookings.index') }}" class="rp-reservations-filter">
                <label class="visually-hidden" for="reservationPaymentFilter">Filter reservations by payment</label>
                <select id="reservationPaymentFilter" name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="">All reservations</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                    <option value="partially_paid" @selected(request('payment_status') === 'partially_paid')>Partially paid</option>
                    <option value="cancelled" @selected(request('payment_status') === 'cancelled')>Cancelled</option>
                </select>
            </form>
        @endif
    </div>

    <div class="rp-booking-list">
        @forelse($bookings as $booking)
            @php
                $paidAmount = (float) $booking->paid_amount;
                $isUnpaid = $paidAmount <= 0.009;
                $isPartial = ! $isUnpaid && ((float) $booking->remaining_balance) > 0.009;
                $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
                $isCancelled = $bookingStatus === 'cancelled';
                $paymentLabel = $isCancelled ? 'Cancelled' : ($isUnpaid ? 'Unpaid' : ($isPartial ? 'Partially paid' : 'Paid'));
            @endphp
            <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-booking-list-item {{ $isUnpaid && ! $isCancelled ? 'is-unpaid' : '' }} {{ $isCancelled ? 'is-cancelled' : '' }}">
                <div class="rp-booking-list-media">
                    @if($booking->accommodation)
                        <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}">
                    @else
                        <div class="rp-booking-list-media-icon"><i class="bi bi-house"></i></div>
                    @endif
                </div>
                <div class="rp-booking-list-body">
                    <div class="rp-booking-list-top">
                        <div>
                            <div class="rp-booking-list-title">{{ $booking->accommodation->name ?? 'Reservation' }}</div>
                            <div class="rp-booking-list-meta">
                                <span class="rp-booking-list-code">{{ $booking->accommodation->type->name ?? 'Accommodation' }} · {{ $booking->short_number }}</span>
                                <span class="rp-booking-pay-badge {{ $isCancelled ? 'is-cancelled' : ($isUnpaid ? 'is-unpaid' : ($isPartial ? 'is-partial' : 'is-paid')) }}">{{ $paymentLabel }}</span>
                            </div>
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
                <div class="rp-booking-list-action">
                    <strong class="rp-booking-list-price">₱{{ number_format((float) $booking->total_amount, 2) }}</strong>
                    <span class="rp-booking-list-view">View Details <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
        @empty
            <div class="rp-booking-empty">
                <div class="rp-booking-empty-icon" aria-hidden="true">
                    <i class="bi bi-calendar3"></i>
                    <span class="rp-booking-empty-x">×</span>
                </div>
                <p>You don't have any reservations yet.</p>
                <a href="{{ route('accommodations.browse') }}" class="rp-booking-empty-btn">Browse Accommodations</a>
            </div>
        @endforelse
    </div>

    @if($bookings->total() > 0)
        <p class="rp-reservations-count">Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} of {{ $bookings->total() }} reservations</p>
    @endif

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
