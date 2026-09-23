@extends('layouts.dashboard')

@section('title', 'Check-in Guest')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-in '.$booking->short_number)
@section('page_subtitle', 'Guest stay details after manual check-in')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $payment = $booking->payments->sortByDesc('payment_date')->first();
    $bookingTotal = (float) $booking->total_amount;
    $bookingPaid = (float) $booking->paid_amount;
    $remainingBalance = (float) $booking->remaining_balance;
    $paymentDisplayStatus = $bookingTotal > 0 && $bookingPaid + 0.009 >= $bookingTotal
        ? 'fully_paid'
        : ($bookingPaid > 0 ? 'partially_paid' : 'unpaid');
    $paymentDisplayLabel = match ($paymentDisplayStatus) {
        'fully_paid' => 'Fully Paid',
        'partially_paid' => 'Partially Paid',
        default => 'Unpaid',
    };
    $methodLabel = $payment
        ? str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method))
        : '—';
    $guestName = $booking->guest_name ?? $booking->guest?->user?->name ?? '—';
    $bookingCode = $booking->short_number ?? $booking->booking_number;
@endphp

<a href="{{ route('front_desk.checkins.index') }}" class="rp-back-link mb-3 d-inline-flex"><i class="bi bi-arrow-left"></i> Back to Check-in</a>

@if($booking->checkIn)
    <div class="alert alert-success">
        Checked in {{ $booking->checkIn->checked_in_at?->format('M d, Y g:i A') ?? '' }}
        @if($booking->checkIn->staff)
            by {{ $booking->checkIn->staff->name }}
        @endif
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="h5 mb-0">Payment info</h2>
                <x-status-badge :status="$paymentDisplayStatus" :label="$paymentDisplayLabel" />
            </div>
            @if($payment)
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted small">Amount</div><div class="fs-4 fw-semibold">₱{{ number_format($payment->amount, 2) }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Method</div><div>{{ $methodLabel }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Reference</div><div>{{ $payment->reference_number ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Date</div><div>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Receipt</div><div>{{ $payment->receipt_number ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Submitted by</div><div>{{ $payment->processor?->name ?? '—' }}</div></div>
                    @if($payment->verified_at)
                        <div class="col-md-6"><div class="text-muted small">Verified / reviewed by</div><div>{{ $payment->verifier?->name ?? '—' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Reviewed at</div><div>{{ $payment->verified_at?->format('M d, Y g:i A') }}</div></div>
                    @endif
                </div>
            @else
                <div class="text-muted">No payment recorded yet.</div>
            @endif
        </div>

        <div class="rp-card mb-4">
            <h2 class="h5 mb-3">Booking</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Booking ID</div><div>{{ $bookingCode }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Guest</div><div>{{ $guestName }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Room</div><div>{{ $booking->accommodation?->name ?? '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Contact</div><div>{{ $booking->contact_number }} · {{ $booking->email }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Dates</div><div>{{ $booking->check_in_date?->format('M d, Y') }} → {{ $booking->check_out_date?->format('M d, Y') }}</div></div>
                <div class="col-md-6">
                    <div class="text-muted small">Booking</div>
                    <div><span class="text-muted">Total</span> ₱{{ number_format($bookingTotal, 2) }}</div>
                    <div><span class="text-muted">Paid</span> ₱{{ number_format($bookingPaid, 2) }}</div>
                    <div><span class="text-muted">Remaining balance</span> ₱{{ number_format($remainingBalance, 2) }}</div>
                </div>
                <div class="col-md-6"><div class="text-muted small">Guests</div><div>{{ $booking->number_of_guests }} / {{ $booking->accommodation?->capacity ?? '—' }} max</div></div>
                @if($booking->special_requests)
                    <div class="col-12"><div class="text-muted small">Special requests</div><div>{{ $booking->special_requests }}</div></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Payment screenshot / proof</h2>
            @if($payment?->proof_url)
                <button type="button" class="rp-payment-proof-link border-0 bg-transparent p-0 w-100 text-start" data-bs-toggle="modal" data-bs-target="#rpCheckInProofModal" aria-label="View payment proof">
                    <img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-img">
                </button>
            @elseif($payment)
                <div class="alert alert-warning mb-0">No screenshot was uploaded for this payment.</div>
            @else
                <div class="alert alert-secondary mb-0">No payment proof yet.</div>
            @endif
        </div>
    </div>
</div>

@if($payment?->proof_url)
<div class="modal fade" id="rpCheckInProofModal" tabindex="-1" aria-labelledby="rpCheckInProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpCheckInProofModalLabel">Payment screenshot</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-modal-img">
                <div class="small text-muted mt-3 mb-0">Ref: {{ $payment->reference_number ?: '—' }} · ₱{{ number_format($payment->amount, 2) }} · {{ $methodLabel }}</div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
