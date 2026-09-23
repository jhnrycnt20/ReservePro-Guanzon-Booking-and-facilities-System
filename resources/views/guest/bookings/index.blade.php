@extends('layouts.public')

@section('title', 'My Reservations')

@section('content')
<div class="container rp-public-page-top pb-4">
    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">My Reservations</h1>
    </div>

    @include('partials.list-filters', [
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Status',
                'empty' => 'All statuses',
                'value' => request('status'),
                'options' => [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                    'checked_in' => 'Checked in',
                    'checked_out' => 'Checked out',
                ],
            ],
        ],
        'searchPlaceholder' => 'Booking Number or Room Name',
        'clearUrl' => route('guest.bookings.index'),
    ])

    <div class="rp-booking-list">
        @forelse($bookings as $booking)
            @php
                $paidAmount = (float) $booking->paid_amount;
                $isUnpaid = $paidAmount <= 0.009;
                $isPartial = ! $isUnpaid && ((float) $booking->remaining_balance) > 0.009;
                $paymentLabel = $isUnpaid ? 'Unpaid' : ($isPartial ? 'Partially paid' : 'Paid');
            @endphp
            <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-booking-list-item {{ $isUnpaid ? 'is-unpaid' : '' }}">
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
                                <span class="rp-booking-list-code">{{ $booking->short_number }}</span>
                                <span class="rp-booking-pay-badge {{ $isUnpaid ? 'is-unpaid' : ($isPartial ? 'is-partial' : 'is-paid') }}">{{ $paymentLabel }}</span>
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
