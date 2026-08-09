@extends('layouts.dashboard')

@section('title', 'Guest Dashboard')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Guest Dashboard')
@section('page_subtitle', 'Track reservations, payments, and stay reports')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="rp-stat"><div class="label">My Bookings</div><div class="value">{{ $stats['bookings'] ?? 0 }}</div></div></div>
    <div class="col-md-3 col-6"><div class="rp-stat"><div class="label">Pending</div><div class="value">{{ $stats['pending'] ?? 0 }}</div></div></div>
    <div class="col-md-3 col-6"><div class="rp-stat"><div class="label">Active Stay</div><div class="value">{{ $stats['checked_in'] ?? 0 }}</div></div></div>
    <div class="col-md-3 col-6"><div class="rp-stat"><div class="label">Open Reports</div><div class="value">{{ $stats['open_reports'] ?? 0 }}</div></div></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('accommodations.browse') }}" class="btn btn-rp-primary"><i class="bi bi-search me-1"></i> Browse Resort</a>
    <a href="{{ route('guest.bookings.index') }}" class="btn btn-rp-soft">My Reservations</a>
    <a href="{{ route('guest.incidents.create') }}" class="btn btn-outline-secondary">Report Issue</a>
</div>

<div class="rp-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Recent Reservations</h2>
        <a href="{{ route('guest.bookings.index') }}" class="small">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Accommodation</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings ?? [] as $booking)
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>{{ $booking->accommodation->name ?? '—' }}</td>
                        <td>{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d, Y') }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td><a href="{{ route('guest.bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No reservations yet. Browse the resort to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
