@extends('layouts.dashboard')

@section('title', 'Check-out Guest')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-out '.$booking->booking_number)
@section('page_subtitle', 'Settle charges and release the accommodation')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="h5 mb-0">{{ $booking->guest_name }}</h2>
                <x-status-badge :status="$booking->status" />
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Room</div>
                    <div>{{ $booking->accommodation->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Stay</div>
                    <div>{{ $booking->check_in_date?->format('M d, Y') }} → {{ $booking->check_out_date?->format('M d, Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Total / Paid</div>
                    <div>₱{{ number_format($booking->total_amount, 2) }} / ₱{{ number_format($booking->paid_amount, 2) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Remaining balance</div>
                    <div class="fw-semibold">₱{{ number_format($booking->remaining_balance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="rp-card">
            <h3 class="h6 mb-3">Complete check-out</h3>
            <form method="POST" action="{{ route('front_desk.checkouts.store', $booking) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Additional charges</label>
                    <input type="number" step="0.01" min="0" name="additional_charges" class="form-control" value="{{ old('additional_charges', 0) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Damages, late checkout, etc.">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-rp-primary w-100">Confirm Check-out</button>
                <a href="{{ route('front_desk.checkouts.index') }}" class="btn btn-rp-soft w-100 mt-2">Back to list</a>
            </form>
        </div>
    </div>
</div>
@endsection
