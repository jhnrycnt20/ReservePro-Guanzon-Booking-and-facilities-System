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
                <div class="rp-cottage-row">
                    <span>Status</span>
                    <x-status-badge :status="$booking->status" />
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
            <h3 class="h6">Actions</h3>
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in']) && $booking->remaining_balance > 0)
                <div class="rp-pay-tip mb-3">
                    <div class="rp-pay-tip-title">How to pay</div>
                    <p class="mb-2">
                        Pay via <strong>GCash</strong>
                        (<strong>{{ $resortSettings['gcash_number'] ?? '09505584607' }}</strong>).
                        Tap below to open GCash, then return here to upload your proof.
                    </p>
                    <div class="rp-gcash-qr-wrap rp-gcash-qr-wrap--compact mb-2">
                        <img src="{{ asset('images/gcash-qr.jpg') }}" alt="GCash QR code" class="rp-gcash-qr">
                    </div>
                    <button
                        type="button"
                        class="rp-avail-btn-primary mb-2"
                        data-rp-open-gcash
                        data-gcash-number="{{ $resortSettings['gcash_number'] ?? '09505584607' }}"
                        data-gcash-amount="{{ number_format(max(0, round(((float) $booking->total_amount) * 0.5, 2)), 2, '.', '') }}"
                    >
                        Open GCash to Pay
                    </button>
                    <p class="mb-0 small text-muted">
                        A 50% deposit is enough to start. Front desk will verify before check-in.
                    </p>
                </div>
            @endif
            <div class="rp-booking-actions">
            @if(
                ! $booking->promo_id
                && ((float) $booking->paid_amount) <= 0
                && in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['pending', 'approved'], true)
            )
                <form method="POST" action="{{ route('guest.bookings.apply_promo', $booking) }}" class="mb-2">
                    @csrf
                    <label class="form-label small mb-1">Have a promo code?</label>
                    <div class="rp-promo-apply">
                        <input type="text" name="promo_code" class="form-control text-uppercase @error('promo_code') is-invalid @enderror" placeholder="Enter code" maxlength="32" required>
                        <button type="submit" class="btn btn-rp-soft">Apply</button>
                    </div>
                    @error('promo_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </form>
            @endif
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in']) && $booking->remaining_balance > 0)
                <a href="{{ route('guest.payments.create', $booking) }}" class="rp-avail-btn-primary">Make Payment</a>
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
                <p class="text-muted small mb-0">No payments recorded yet. Tap Make Payment after sending GCash.</p>
            @endforelse
        </div>
    </div>
</div>
</div>
@endsection
