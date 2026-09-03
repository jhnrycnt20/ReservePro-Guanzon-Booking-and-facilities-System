@extends('layouts.dashboard')

@section('title', 'Reservation '.$booking->booking_number)
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Reservation '.$booking->booking_number)
@section('page_subtitle', 'Status, payment, and stay details')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Bookings</a>

@include('partials.booking-tracker', ['activeStep' => 'confirmation'])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-flow-card mb-4">
            <div class="mb-3">
                <h2 class="h5 mb-1">{{ $booking->accommodation->name }}</h2>
                <div class="text-muted">{{ $booking->check_in_date->format('M d, Y') }} → {{ $booking->check_out_date->format('M d, Y') }}</div>
            </div>
            <div class="row g-3">
                <div class="col-md-3"><div class="text-muted small">Guests</div><div>{{ $booking->number_of_guests }} ({{ $booking->adults }} adults, {{ $booking->children }} children)</div></div>
                <div class="col-md-3"><div class="text-muted small">Total</div><div>₱{{ number_format($booking->total_amount, 2) }}</div></div>
                <div class="col-md-3"><div class="text-muted small">Remaining balance</div><div>₱{{ number_format($booking->remaining_balance, 2) }}</div></div>
                <div class="col-md-3"><div class="text-muted small">Status</div><div>{{ ucfirst($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) }}</div></div>
            </div>
            @if($booking->special_requests)
                <hr>
                <div class="text-muted small">Special requests</div>
                <div>{{ $booking->special_requests }}</div>
            @endif
            @if($booking->rejection_reason)
                <div class="alert alert-danger mt-3 mb-0">Rejected: {{ $booking->rejection_reason }}</div>
            @endif
        </div>

        <div class="rp-flow-card">
            <h3 class="h6">Payments</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Status</th><th>Receipt</th></tr></thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date?->format('M d, Y') }}</td>
                                <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</td>
                                <td>₱{{ number_format($payment->amount, 2) }}</td>
                                <td><x-status-badge :status="$payment->status" /></td>
                                <td>{{ $payment->receipt_number ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-flow-card mb-3">
            <h3 class="h6">Actions</h3>
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in']) && $booking->remaining_balance > 0)
                <a href="{{ route('guest.payments.create', $booking) }}" class="btn btn-rp-primary w-100 mb-2">Make Payment</a>
            @endif
            @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'pending')
                <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}">
                    @csrf
                    <button class="btn btn-outline-danger w-100" onclick="return confirm('Cancel this reservation?')">Cancel Reservation</button>
                </form>
            @endif
            @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'checked_in')
                <a href="{{ route('guest.incidents.create', ['booking_id' => $booking->id]) }}" class="btn btn-rp-soft w-100 mt-2">Report Issue</a>
            @endif
            @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'checked_out' && !$booking->feedback)
                <a href="{{ route('guest.feedback.create', $booking) }}" class="btn btn-rp-soft w-100 mt-2">Leave Feedback</a>
            @endif
        </div>
    </div>
</div>
@endsection
