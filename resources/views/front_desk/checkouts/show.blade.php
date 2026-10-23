@extends('layouts.dashboard')

@section('title', 'Check-out Guest')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-out '.$booking->booking_number)
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $isEarly = $booking->isEarlyCheckOut();
    $hasOpenIncidents = $booking->hasOpenIncidents();
    $checkOutTime = \Carbon\Carbon::createFromFormat('H:i', (string) config('resort.check_out_time', '12:00'))->format('g:i A');
@endphp
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
                    <div>{{ $booking->check_in_date?->format('M d, Y') }} → {{ $booking->check_out_date?->format('M d, Y') }} · {{ $checkOutTime }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Total / Paid</div>
                    <div>₱{{ number_format($booking->total_amount, 2) }} / ₱{{ number_format($booking->paid_amount, 2) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Remaining balance</div>
                    <div class="fw-semibold">₱{{ number_format($booking->remaining_balance, 2) }}</div>
                </div>
                <div class="col-12">
                    @if($booking->checkOutDue())
                        <span class="rp-resv-pill is-due">Due now</span>
                    @elseif($isEarly)
                        <span class="rp-resv-pill is-early">Early check-out</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="rp-card">
            <h3 class="h6 mb-3">{{ $isEarly ? 'Early check-out' : 'Complete check-out' }}</h3>
            @if($hasOpenIncidents)
                <div class="alert alert-warning py-2">Resolve open incident reports before check-out.</div>
            @endif
            @if((float) $booking->remaining_balance > 0.009)
                <div class="alert alert-warning py-2">Settle the remaining balance first (Collect balance on Reservations).</div>
            @endif
            <form method="POST" action="{{ route('front_desk.checkouts.store', $booking) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Additional charges</label>
                    <input type="number" step="0.01" min="0" name="additional_charges" class="form-control" value="{{ old('additional_charges', 0) }}" @disabled($hasOpenIncidents)>
                    <div class="form-text">Extras must be collected before check-out can finish.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $isEarly ? 'Reason for early check-out' : 'Notes (optional)' }}</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="{{ $isEarly ? 'Required — why the guest is leaving early…' : 'Damages, late checkout, etc.' }}" @required($isEarly) @disabled($hasOpenIncidents)>{{ old('notes') }}</textarea>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('incidents')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('payment')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-rp-primary w-100" @disabled($hasOpenIncidents || (float) $booking->remaining_balance > 0.009)>Confirm Check-out</button>
                <a href="{{ route('front_desk.reservations.index', ['tab' => 'check_out']) }}" class="btn btn-rp-soft w-100 mt-2">Back to list</a>
            </form>
        </div>
    </div>
</div>
@endsection
