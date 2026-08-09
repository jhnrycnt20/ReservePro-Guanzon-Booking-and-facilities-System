@extends('layouts.dashboard')

@section('title', 'Review Reservation')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Review '.$booking->booking_number)
@section('page_subtitle', 'Approve or reject after verifying details and availability')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="h5 mb-0">{{ $booking->guest_name }}</h2>
                <x-status-badge :status="$booking->status" />
            </div>
            <div class="row g-3">
                <div class="col-md-4"><div class="text-muted small">Contact</div><div>{{ $booking->contact_number }}</div><div>{{ $booking->email }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Accommodation</div><div>{{ $booking->accommodation->name }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Dates</div><div>{{ $booking->check_in_date->format('M d, Y') }} → {{ $booking->check_out_date->format('M d, Y') }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Guests</div><div>{{ $booking->number_of_guests }} / capacity {{ $booking->accommodation->capacity }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Total</div><div>₱{{ number_format($booking->total_amount, 2) }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Paid / Balance</div><div>₱{{ number_format($booking->paid_amount, 2) }} / ₱{{ number_format($booking->remaining_balance, 2) }}</div></div>
            </div>
            @if($booking->special_requests)
                <hr><div class="text-muted small">Special requests</div><div>{{ $booking->special_requests }}</div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'pending')
            <div class="rp-card mb-3">
                <form method="POST" action="{{ route('front_desk.reservations.approve', $booking) }}">
                    @csrf
                    <button class="btn btn-success w-100 mb-2">Approve Reservation</button>
                </form>
                <form method="POST" action="{{ route('front_desk.reservations.reject', $booking) }}">
                    @csrf
                    <textarea name="rejection_reason" class="form-control mb-2" rows="3" placeholder="Rejection reason" required></textarea>
                    <button class="btn btn-outline-danger w-100">Reject Reservation</button>
                </form>
            </div>
        @endif

        @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'approved')
            <a href="{{ route('front_desk.checkins.show', $booking) }}" class="btn btn-rp-primary w-100">Proceed to Check-in</a>
        @endif
    </div>
</div>
@endsection
