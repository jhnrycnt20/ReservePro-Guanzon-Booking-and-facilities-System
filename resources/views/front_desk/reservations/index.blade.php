@extends('layouts.dashboard')

@section('title', 'Reservations')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Reservations')
@section('page_subtitle', 'Review the reservation queue and manage bookings')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card mb-3">
    <form method="GET" class="row g-2 align-items-end" data-rp-live-filter>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" data-rp-live-filter-change>
                <option value="">All</option>
                @foreach(['pending','approved','rejected','cancelled','checked_in','checked_out'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="e.g. BK-7K2M or guest name" data-rp-live-filter-q autocomplete="off">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-rp-primary flex-grow-1">Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('front_desk.reservations.index') }}" class="btn btn-rp-soft">Clear</a>
            @endif
        </div>
    </form>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Booking</th><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><span class="rp-booking-code" title="{{ $booking->booking_number }}">{{ $booking->short_number }}</span></td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation->name }}</td>
                        <td>{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d') }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td><a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-primary">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No reservations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>
@endsection
