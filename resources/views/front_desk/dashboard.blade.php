@extends('layouts.dashboard')

@section('title', 'Front Desk Dashboard')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Front Desk Dashboard')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today's Check-ins</div><div class="value">{{ $stats['today_checkins'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today's Check-outs</div><div class="value">{{ $stats['today_checkouts'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Reserved (awaiting pay)</div><div class="value">{{ $stats['reserved'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Booked (50%+)</div><div class="value">{{ $stats['booked'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Ready for check-in</div><div class="value">{{ $stats['ready_checkin'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Occupied</div><div class="value">{{ $stats['occupied'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Available</div><div class="value">{{ $stats['available'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Incidents</div><div class="value">{{ $stats['pending_incidents'] ?? 0 }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="rp-card">
            <h2 class="h5 mb-3">Reservations (Booked &amp; Ready)</h2>
            <div class="table-responsive">
                <table class="table align-middle" style="table-layout: fixed;">
                    <thead><tr><th style="width: 28%;">Guest</th><th style="width: 24%;">Room</th><th style="width: 28%;">Dates</th><th style="width: 20%;"></th></tr></thead>
                    <tbody>
                        @forelse($pendingReservations ?? [] as $booking)
                            <tr>
                                <td>{{ $booking->guest_name }}</td>
                                <td>{{ $booking->accommodation->name ?? '—' }}</td>
                                <td>{{ $booking->check_in_date->format('M d') }} → {{ $booking->check_out_date->format('M d') }}</td>
                                <td class="text-end"><a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-soft">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No open reservations on the queue.</td></tr>
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
                        <div class="fw-semibold">{{ $activity->label }}</div>
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
