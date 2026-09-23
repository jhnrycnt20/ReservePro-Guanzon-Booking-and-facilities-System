@extends('layouts.public')

@section('title', 'Payments')

@section('content')
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reservations</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Payments</h1>
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
                    'verified' => 'Verified',
                    'rejected' => 'Rejected',
                ],
            ],
        ],
        'searchPlaceholder' => 'Booking Number, Room, or Reference',
        'clearUrl' => route('guest.payments.index'),
    ])

    <div class="rp-booking-list">
        @forelse($payments as $payment)
            <a href="{{ route('guest.payments.show', $payment) }}" class="rp-booking-list-item">
                <div class="rp-booking-list-media">
                    @if($payment->booking?->accommodation)
                        <img src="{{ $payment->booking->accommodation->image_url }}" alt="{{ $payment->booking->accommodation->name }}">
                    @endif
                </div>
                <div class="rp-booking-list-body">
                    <div class="rp-booking-list-top">
                        <div>
                            <div class="rp-booking-list-title">{{ $payment->booking?->accommodation?->name ?? $payment->booking?->booking_number ?? 'Payment' }}</div>
                        </div>
                    </div>
                    <div class="rp-payment-mini-top">
                        <span class="rp-payment-mini-amount">₱{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="rp-payment-mini-meta">
                        {{ $payment->payment_date?->format('M d, Y') ?? $payment->created_at?->format('M d, Y') }} &middot; {{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}
                    </div>
                    @if($payment->reference_number)
                        <div class="rp-payment-mini-receipt">Reference: {{ $payment->reference_number }}</div>
                    @endif
                </div>
                <div class="rp-booking-list-arrow"><i class="bi bi-chevron-right"></i></div>
            </a>
        @empty
            <div class="rp-booking-empty">
                <i class="bi bi-cash-stack"></i>
                <p>You haven't made any payments yet.</p>
                <a href="{{ route('guest.bookings.index') }}" class="rp-avail-btn-primary">View My Reservations</a>
            </div>
        @endforelse
    </div>

    @if($payments->hasPages())
        <div class="rp-simple-pagination mt-4">
            @if($payments->onFirstPage())
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $payments->previousPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-left"></i></a>
            @endif

            @for($page = 1; $page <= $payments->lastPage(); $page++)
                @if($page === $payments->currentPage())
                    <span class="rp-simple-pagination-num is-active">{{ $page }}</span>
                @else
                    <a href="{{ $payments->url($page) }}" class="rp-simple-pagination-num">{{ $page }}</a>
                @endif
            @endfor

            @if($payments->hasMorePages())
                <a href="{{ $payments->nextPageUrl() }}" class="rp-simple-pagination-arrow"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="rp-simple-pagination-arrow is-disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    @endif
</div>
@endsection
