@extends('layouts.dashboard')

@section('title', 'Front Desk Dashboard')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Front Desk Dashboard')
@section('page_subtitle', 'Reservations, payments, check-in/out, and guest support')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today's Check-ins</div><div class="value">{{ $stats['today_checkins'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today's Check-outs</div><div class="value">{{ $stats['today_checkouts'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Reservations</div><div class="value">{{ $stats['pending'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Payments</div><div class="value">{{ $stats['pending_payments'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Approved</div><div class="value">{{ $stats['approved'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Occupied</div><div class="value">{{ $stats['occupied'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Available</div><div class="value">{{ $stats['available'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Incidents</div><div class="value">{{ $stats['pending_incidents'] ?? 0 }}</div></div></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('front_desk.walkins.create') }}" class="btn btn-rp-primary"><i class="bi bi-person-walking me-1"></i> New Walk-in</a>
    <a href="{{ route('front_desk.reservations.index') }}" class="btn btn-rp-soft">View Reservations</a>
    <a href="{{ route('front_desk.checkins.index') }}" class="btn btn-outline-secondary">Check-in</a>
    <a href="{{ route('front_desk.checkouts.index') }}" class="btn btn-outline-secondary">Check-out</a>
    <a href="{{ route('front_desk.incidents.index') }}" class="btn btn-outline-secondary">View Reports</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="rp-card">
            <h2 class="h5 mb-3">Pending Reservations Queue</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Guest</th><th>Room</th><th>Dates</th><th></th></tr></thead>
                    <tbody>
                        @forelse($pendingReservations ?? [] as $booking)
                            <tr>
                                <td>{{ $booking->guest_name }}</td>
                                <td>{{ $booking->accommodation->name ?? '—' }}</td>
                                <td>{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d') }}</td>
                                <td><a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-primary">Review</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No pending reservations.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="rp-card">
            <h2 class="h5 mb-3">Recent Activities</h2>
            <ul class="list-group list-group-flush">
                @forelse($recentActivities ?? [] as $activity)
                    <li class="list-group-item px-0">
                        <div class="fw-semibold">{{ $activity->action }}</div>
                        <div class="small text-muted">{{ $activity->created_at->diffForHumans() }}</div>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">No recent activity logged.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
