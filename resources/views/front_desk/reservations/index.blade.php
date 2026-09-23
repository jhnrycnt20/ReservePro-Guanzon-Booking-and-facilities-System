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
        <div class="col-md-9">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="e.g. BK-7K2M or guest name" data-rp-live-filter-q autocomplete="off">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-rp-primary w-100">Filter</button>
        </div>
    </form>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead><tr><th style="width: 14%;">Booking</th><th style="width: 20%;">Guest</th><th style="width: 18%;">Room</th><th style="width: 20%;">Dates</th><th style="width: 12%;">Status</th><th style="width: 16%;"></th></tr></thead>
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
@endsection
