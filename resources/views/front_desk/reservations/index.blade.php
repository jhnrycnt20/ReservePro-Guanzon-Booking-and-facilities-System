@extends('layouts.dashboard')

@section('title', 'Reservations')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Reservations')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card mb-3">
    <form method="GET" class="row g-2 align-items-end" data-rp-live-filter>
        <div class="col-12 col-md-9">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="e.g. BK-7K2M or guest name" data-rp-live-filter-q autocomplete="off">
        </div>
        <div class="col-12 col-md-3">
            <button type="submit" class="btn btn-rp-primary w-100">Filter</button>
        </div>
    </form>
</div>

{{-- Desktop table --}}
<div class="rp-card d-none d-md-block">
    <div class="table-responsive">
        <table class="table align-middle rp-desk-table">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><span class="rp-booking-code" title="{{ $booking->booking_number }}">{{ $booking->short_number }}</span></td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation?->name ?? '—' }}</td>
                        <td>{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d') }}</td>
                        <td><x-booking-desk-status :booking="$booking" plain /></td>
                        <td class="text-end">
                            <a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-soft">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No open reservations — all stays are fully paid or completed.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>

{{-- Mobile cards --}}
<div class="d-md-none">
    @forelse($bookings as $booking)
        <div class="rp-mobile-list-card">
            <div class="rp-mobile-list-card__top">
                <span class="rp-booking-code">{{ $booking->short_number }}</span>
                <x-booking-desk-status :booking="$booking" plain />
            </div>
            <div class="rp-mobile-list-card__title">{{ $booking->guest_name }}</div>
            <div class="rp-mobile-list-card__meta">{{ $booking->accommodation?->name ?? '—' }}</div>
            <div class="rp-mobile-list-card__meta">{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d') }}</div>
            <a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-soft w-100 mt-2">View</a>
        </div>
    @empty
        <div class="rp-card text-muted">No open reservations — all stays are fully paid or completed.</div>
    @endforelse
    @if(method_exists($bookings, 'links'))
        <div class="mt-3">{{ $bookings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
