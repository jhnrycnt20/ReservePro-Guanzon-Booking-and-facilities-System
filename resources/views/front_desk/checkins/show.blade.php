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
    <div class="col-lg-5">
        <div class="rp-card">
            <h3 class="h6 mb-3">Complete check-in</h3>
            @unless($hasDeposit)
                <div class="alert alert-warning small">
                    Guest needs at least ₱{{ number_format($depositNeeded, 2) }} verified before check-in.
                    Remaining ₱{{ number_format($booking->remaining_balance, 2) }} can be collected now or later.
                </div>
            @endunless
            <form method="POST" action="{{ route('front_desk.checkins.store', $booking) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="ID checked, key issued, etc.">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-rp-primary w-100" @disabled(! $hasDeposit)>Confirm Check-in</button>
                <a href="{{ route('front_desk.checkins.index') }}" class="btn btn-rp-soft w-100 mt-2">Back to list</a>
            </form>
        </div>
    </div>
</div>
@endsection
