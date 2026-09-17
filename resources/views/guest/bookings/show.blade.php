@extends('layouts.public')

@section('title', 'Reservation '.$booking->booking_number)

@section('content')
<div class="container rp-public-page-top pb-4">
<a href="{{ route('guest.bookings.index') }}" class="rp-back-link" data-rp-history-back><i class="bi bi-arrow-left"></i> Back</a>

<div class="rp-page-intro">
    <h1 class="rp-page-intro-title">Reservation {{ $booking->short_number }}</h1>
</div>

@include('partials.booking-tracker', ['activeStep' => 'confirmation'])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-cottage-card rp-cottage-card--static rp-booking-confirm-card mb-4">
            <div class="rp-cottage-media">
                <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}">
            </div>
            <div class="rp-cottage-card-body">
                <div class="rp-cottage-title">{{ $booking->accommodation->name }}</div>
                <div class="rp-booking-list-dates mb-2">
                    <div class="rp-booking-list-date-block">
                        <span class="rp-booking-list-date-label">Check-in</span>
                        <span class="rp-booking-list-date-value">{{ $booking->check_in_date->format('M d, Y') }}</span>
                    </div>
                    <div class="rp-booking-list-date-block">
                        <span class="rp-booking-list-date-label">Check-out</span>
                        <span class="rp-booking-list-date-value">{{ $booking->check_out_date->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="rp-cottage-row">
                    <span>Guests</span>
                    <span>{{ $booking->number_of_guests }} ({{ $booking->adults }} adults, {{ $booking->children }} children)</span>
                </div>
                <div class="rp-cottage-row">
                    <span>Total</span>
                    <span>₱{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                    <div class="rp-cottage-row">
                        <span>Promo</span>
                        <span>
                            <code>{{ $booking->promo_code }}</code>
                            @if($booking->discount_percent)
                                ({{ rtrim(rtrim(number_format((float) $booking->discount_percent, 2), '0'), '.') }}% off)
                            @endif
                        </span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>You saved</span>
                        <span>₱{{ number_format((float) $booking->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="rp-cottage-row">
                    <span>Remaining balance</span>
                    <span>₱{{ number_format($booking->remaining_balance, 2) }}</span>
                </div>
            </div>
        </div>

        @if($booking->special_requests || $booking->rejection_reason)
            <div class="rp-flow-card mb-4">
                @if($booking->special_requests)
                    <h3 class="h6">Special requests</h3>
                    <p class="mb-0">{{ $booking->special_requests }}</p>
                @endif
                @if($booking->rejection_reason)
                    <div class="alert alert-danger {{ $booking->special_requests ? 'mt-3' : '' }} mb-0">Rejected: {{ $booking->rejection_reason }}</div>
                @endif
            </div>
        @endif

    </div>
    <div class="col-lg-4">
        <div class="rp-flow-card mb-3">
            <div class="rp-booking-actions">
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in']) && $booking->remaining_balance > 0)
                <a href="{{ route('guest.payments.create', $booking) }}" class="rp-avail-btn-primary">Proceed to Payment</a>
            @endif
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['pending', 'approved']))
                <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}">
                    @csrf
                    <button type="submit" class="rp-avail-btn-secondary rp-avail-btn-secondary--danger" data-rp-confirm-click="Cancel this reservation?">Cancel Reservation</button>
                </form>
            @endif
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in', 'checked_out']))
                <a href="{{ route('guest.incidents.create', ['booking_id' => $booking->id]) }}" class="rp-avail-btn-secondary rp-avail-btn-secondary--danger">Report Issue</a>
            @endif
            @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'checked_out' && !$booking->feedback)
                <a href="{{ route('guest.feedback.create', $booking) }}" class="rp-avail-btn-secondary">Leave Feedback</a>
            @endif
            </div>
        </div>

        <div class="rp-flow-card">
            <h3 class="h6">Payments</h3>
            @forelse($booking->payments as $payment)
                <div class="rp-payment-mini">
                    <div class="rp-payment-mini-top">
                        <span class="rp-payment-mini-amount">₱{{ number_format($payment->amount, 2) }}</span>
                        <x-status-badge :status="$payment->status" />
                    </div>
                    <div class="rp-payment-mini-meta">
                        {{ $payment->payment_date?->format('M d, Y') }} &middot; {{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}
                    </div>
                    @if($payment->reference_number)
                        <div class="rp-payment-mini-receipt">Ref: {{ $payment->reference_number }}</div>
                    @endif
                    @if($payment->receipt_number)
                        <div class="rp-payment-mini-receipt">Receipt: {{ $payment->receipt_number }}</div>
                    @endif
                </div>
            @empty
                <p class="text-muted small mb-0">No payments recorded yet. Tap Proceed to Payment after sending GCash.</p>
            @endforelse
        </div>
    </div>
</div>
</div>
@endsection
