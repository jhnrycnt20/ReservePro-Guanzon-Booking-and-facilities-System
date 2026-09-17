@extends('layouts.dashboard')

@section('title', 'Check-in Guest')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-in '.$booking->short_number)
@section('page_subtitle', 'Confirm arrival and mark accommodation occupied')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $depositNeeded = round(((float) $booking->total_amount) * 0.5, 2);
    $hasDeposit = ((float) $booking->paid_amount) + 0.009 >= $depositNeeded;
    $latestPayment = $booking->payments->sortByDesc('created_at')->first();
@endphp
@if(request()->boolean('created'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Thank you for booking with us!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<a href="{{ route('front_desk.checkins.index') }}" class="rp-back-link mb-3 d-inline-flex"><i class="bi bi-arrow-left"></i> Back to check-ins</a>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card">
            <div class="mb-3">
                <h2 class="h5 mb-0">{{ $booking->guest_name }}</h2>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Room</div>
                    <div>{{ $booking->accommodation->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Contact</div>
                    <div>{{ $booking->contact_number }} · {{ $booking->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Stay</div>
                    <div>{{ $booking->check_in_date?->format('M d, Y') }} → {{ $booking->check_out_date?->format('M d, Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Guests</div>
                    <div>{{ $booking->number_of_guests }} ({{ $booking->adults }} adults, {{ $booking->children }} children)</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Total / Paid</div>
                    <div>₱{{ number_format($booking->total_amount, 2) }} / ₱{{ number_format($booking->paid_amount, 2) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Payment method</div>
                    <div>{{ $latestPayment?->payment_method ? str_replace('_', ' ', ucfirst($latestPayment->payment_method instanceof \BackedEnum ? $latestPayment->payment_method->value : $latestPayment->payment_method)) : '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Remaining balance</div>
                    <div class="fw-semibold">₱{{ number_format($booking->remaining_balance, 2) }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Deposit requirement (50%)</div>
                    <div>
                        ₱{{ number_format($depositNeeded, 2) }}
                        @if($hasDeposit)
                            <span class="badge text-bg-success ms-1">Met</span>
                        @else
                            <span class="badge text-bg-warning ms-1">Not yet verified</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($booking->special_requests)
                <hr>
                <div class="text-muted small">Special requests</div>
                <p class="mb-0">{{ $booking->special_requests }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
