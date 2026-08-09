@extends('layouts.dashboard')

@section('title', 'Make Payment')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Make Payment')
@section('page_subtitle', 'Booking '.$booking->booking_number)
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="rp-card">
            <div class="mb-3">
                <div class="text-muted small">Remaining balance</div>
                <div class="fs-3" style="font-family: var(--rp-display);">₱{{ number_format($booking->remaining_balance, 2) }}</div>
            </div>
            <form method="POST" action="{{ route('guest.payments.store', $booking) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $booking->remaining_balance }}" name="amount" class="form-control" value="{{ old('amount', $booking->remaining_balance) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment method</label>
                    <select name="payment_method" class="form-select" required>
                        @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference number</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment date</label>
                    <input type="datetime-local" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
                <button class="btn btn-rp-primary w-100">Submit Payment</button>
            </form>
        </div>
    </div>
</div>
@endsection
