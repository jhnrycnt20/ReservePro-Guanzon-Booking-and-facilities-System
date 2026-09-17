@extends('layouts.dashboard')

@section('title', 'Reservation Details')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Reservation '.$booking->short_number)
@section('page_subtitle', 'Guest and payment details — no approval needed')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status;
    $isReserved = in_array($bookingStatus, ['pending', 'approved'], true) && ! $booking->hasMetDepositRequirement();
@endphp
<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card">
            <div class="mb-3">
                <h2 class="h5 mb-0">{{ $booking->guest_name }}</h2>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><div class="text-muted small">Contact</div><div>{{ $booking->contact_number }}</div><div>{{ $booking->email }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Accommodation</div><div>{{ $booking->accommodation?->name ?? '—' }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Dates</div><div>{{ $booking->check_in_date->format('M d, Y') }} → {{ $booking->check_out_date->format('M d, Y') }}</div></div>
                <div class="col-md-4"><div class="text-muted small">Guests</div><div>{{ $booking->number_of_guests }} / capacity {{ $booking->accommodation?->capacity ?? '—' }}</div></div>
                <div class="col-md-4">
                    <div class="text-muted small">Total</div>
                    <div>₱{{ number_format($booking->total_amount, 2) }}</div>
                    @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                        @if($booking->original_amount)
                            <div class="small text-muted text-decoration-line-through">₱{{ number_format((float) $booking->original_amount, 2) }}</div>
                        @endif
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Paid / Balance</div>
                    <div>₱{{ number_format($booking->paid_amount, 2) }} / ₱{{ number_format($booking->remaining_balance, 2) }}</div>
                    @if($isReserved)
                        <div class="small text-muted mt-1">Needs ₱{{ number_format($booking->depositRequiredAmount(), 2) }} (50%) to become Booked; full payment required for check-in (from 2:00 PM).</div>
                    @elseif($booking->hasMetDepositRequirement() && ! $booking->isFullyPaid())
                        <div class="small text-muted mt-1">Booked — pay remaining balance for check-in (from 2:00 PM on arrival).</div>
                    @endif
                </div>
                @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                    <div class="col-md-4">
                        <div class="text-muted small">Promo code</div>
                        <div><code>{{ $booking->promo_code ?: ($booking->promo?->code ?? '—') }}</code></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Discount</div>
                        <div>
                            @if($booking->discount_percent)
                                {{ rtrim(rtrim(number_format((float) $booking->discount_percent, 2), '0'), '.') }}% off
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Amount saved</div>
                        <div class="text-success">₱{{ number_format((float) $booking->discount_amount, 2) }}</div>
                    </div>
                @endif
            </div>
            @if($booking->special_requests)
                <hr><div class="text-muted small">Special requests</div><div>{{ $booking->special_requests }}</div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-card">
            <h2 class="h5 mb-3">Actions</h2>
            <div class="d-grid gap-2">
                @if($isReserved)
                    @can('cancel', $booking)
                        <form method="POST" action="{{ route('front_desk.reservations.cancel', $booking) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100" data-rp-confirm-click="Cancel this reservation?">Cancel Reservation</button>
                        </form>
                    @endcan
                @endif

                <a href="{{ $isReserved ? route('front_desk.reservations.index') : route('front_desk.checkins.index') }}" class="btn btn-rp-soft w-100">Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
