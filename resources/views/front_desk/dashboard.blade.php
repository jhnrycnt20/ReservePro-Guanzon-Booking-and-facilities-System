@extends('layouts.dashboard')

@section('title', 'Front Desk Dashboard')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Welcome back, ' . strtok(auth()->user()->name, ' '))
@section('page_subtitle', 'Today’s arrivals, departures, and reservations at a glance.')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-staff-dashboard">
    <div class="rp-staff-stat-grid">
        <x-staff-stat label="Check-ins today" :value="$stats['today_checkins']" icon="bi-box-arrow-in-right" hint="Scheduled arrivals" />
        <x-staff-stat label="Check-outs today" :value="$stats['today_checkouts']" icon="bi-box-arrow-right" hint="Scheduled departures" />
        <x-staff-stat label="Ready for check-in" :value="$stats['ready_checkin']" icon="bi-calendar-check" hint="Approved and fully paid" />
        <x-staff-stat label="Pending incidents" :value="$stats['pending_incidents']" icon="bi-clipboard2-pulse" hint="Awaiting front desk action" />
    </div>

    <section class="rp-staff-panel rp-staff-chart-panel" aria-labelledby="desk-chart-title">
        <div class="rp-staff-panel-head">
            <div>
                <h2 id="desk-chart-title">Stay flow</h2>
                <p>Scheduled check-ins and check-outs over seven days</p>
            </div>
            <a href="{{ route('front_desk.reservations.index') }}" class="rp-staff-text-link">Reservations <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="rp-staff-chart-wrap"><canvas id="deskStayChart" aria-label="Daily check-ins and check-outs chart" role="img"></canvas></div>
    </section>

    <div class="rp-staff-columns">
        <section class="rp-staff-panel" aria-labelledby="desk-reservations-title">
            <div class="rp-staff-panel-head">
                <div>
                    <h2 id="desk-reservations-title">Active reservations</h2>
                    <p>Booked and ready stays in the queue</p>
                </div>
            </div>
            <div class="rp-staff-queue">
                @forelse($pendingReservations as $booking)
                    @php($paymentProgress = $booking->total_amount > 0 ? min(100, round($booking->paid_amount / $booking->total_amount * 100)) : 0)
                    @php($reservationStatus = $booking->status === \App\Enums\BookingStatus::CheckedIn ? 'Checked in' : ($booking->isFullyPaid() ? 'Ready' : 'Booked'))
                    <a href="{{ route('front_desk.reservations.show', $booking) }}" class="rp-staff-reservation">
                        <div class="rp-staff-reservation-top">
                            <strong>{{ $booking->guest_name }}</strong>
                            <span class="rp-staff-pill">{{ $reservationStatus }}</span>
                        </div>
                        <p>{{ $booking->accommodation->name ?? 'Accommodation' }} · {{ $booking->check_in_date->format('M j') }} – {{ $booking->check_out_date->format('M j') }}</p>
                        <div class="rp-staff-progress-label"><span>Payment progress</span><span>{{ $paymentProgress }}%</span></div>
                        <div class="rp-staff-progress" role="progressbar" aria-label="Payment progress for {{ $booking->guest_name }}" aria-valuenow="{{ $paymentProgress }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $paymentProgress }}%"></span></div>
                    </a>
                @empty
                    <div class="rp-staff-empty">No active reservations in the queue.</div>
                @endforelse
            </div>
        </section>

        <section class="rp-staff-panel" aria-labelledby="desk-activity-title">
            <div class="rp-staff-panel-head">
                <div>
                    <h2 id="desk-activity-title">Recent activity</h2>
                    <p>Latest front desk updates</p>
                </div>
            </div>
            <ol class="rp-staff-activity-list">
                @forelse($recentActivities as $activity)
                    <li>
                        <span class="rp-staff-activity-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
                        <div class="rp-staff-activity-copy"><strong>{{ $activity->label }}</strong></div>
                        <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time>
                    </li>
                @empty
                    <li class="rp-staff-empty">No recent activity logged.</li>
                @endforelse
            </ol>
            <div class="rp-staff-panel-divider"></div>
            <div class="rp-staff-metric-list">
                <div class="rp-staff-metric"><span>Awaiting payment</span><strong>{{ $stats['reserved'] }}</strong></div>
                <div class="rp-staff-metric"><span>Booked (50%+)</span><strong>{{ $stats['booked'] }}</strong></div>
                <div class="rp-staff-metric"><span>Occupied accommodations</span><strong>{{ $stats['occupied'] }}</strong></div>
                <div class="rp-staff-metric"><span>Available accommodations</span><strong>{{ $stats['available'] }}</strong></div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const deskChartCanvas = document.getElementById('deskStayChart');
if (deskChartCanvas && window.Chart) {
    const context = deskChartCanvas.getContext('2d');
    const fill = context.createLinearGradient(0, 0, 0, 260);
    fill.addColorStop(0, 'rgba(31, 75, 93, 0.16)');
    fill.addColorStop(1, 'rgba(31, 75, 93, 0.01)');
    new Chart(context, {
        type: 'line',
        data: { labels: @json($chart['days']), datasets: [
            { label: 'Check-ins', data: @json($chart['checkins']), borderColor: '#1f4b5d', backgroundColor: fill, fill: true, tension: .35, borderWidth: 2, pointRadius: 3 },
            { label: 'Check-outs', data: @json($chart['checkouts']), borderColor: '#72b9ac', borderDash: [5, 5], fill: false, tension: .35, borderWidth: 2, pointRadius: 3 }
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
}
</script>
@endpush
