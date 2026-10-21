@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')
@section('theme', 'admin')
@section('role_label', 'Admin')
@section('page_title', 'Welcome back, ' . strtok(auth()->user()->name, ' '))
@section('page_subtitle', 'A live overview of reservations, revenue, and resort operations.')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-staff-dashboard">
    <div class="rp-staff-stat-grid">
        <x-staff-stat label="Total reservations" :value="number_format($stats['total_reservations'])" icon="bi-journal-check" hint="All recorded bookings" />
        <x-staff-stat label="Reservations today" :value="number_format($stats['today_reservations'])" icon="bi-calendar-event" hint="Created today" />
        <x-staff-stat label="Total guests" :value="number_format($stats['total_guests'])" icon="bi-people" hint="Registered guest profiles" />
        <x-staff-stat label="Verified revenue" :value="'₱' . number_format($stats['total_revenue'], 0)" icon="bi-graph-up-arrow" hint="From verified payments" />
    </div>

    <section class="rp-staff-panel rp-staff-chart-panel" aria-labelledby="admin-chart-title">
        <div class="rp-staff-panel-head">
            <div>
                <h2 id="admin-chart-title">Reservation trend</h2>
                <p>Bookings created in the last six months</p>
            </div>
            <a href="{{ route('admin.analytics.index') }}" class="rp-staff-text-link">View analytics <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="rp-staff-chart-wrap"><canvas id="adminReservationsChart" aria-label="Monthly reservations chart" role="img"></canvas></div>
    </section>

    <div class="rp-staff-columns">
        <section class="rp-staff-panel" aria-labelledby="admin-operations-title">
            <div class="rp-staff-panel-head">
                <div>
                    <h2 id="admin-operations-title">Resort operations</h2>
                    <p>Current accommodation and incident status</p>
                </div>
            </div>
            <div class="rp-staff-metric-list">
                <div class="rp-staff-metric"><span>Available accommodations</span><strong>{{ $stats['available'] }}</strong></div>
                <div class="rp-staff-metric"><span>Occupied accommodations</span><strong>{{ $stats['occupied'] }}</strong></div>
                <div class="rp-staff-metric"><span>Pending reports</span><strong>{{ $stats['pending_reports'] }}</strong></div>
                <div class="rp-staff-metric"><span>Resolved reports</span><strong>{{ $stats['resolved_reports'] }}</strong></div>
            </div>
            <div class="rp-staff-progress-label"><span>Occupancy rate</span><strong>{{ number_format($charts['occupancy_rate'], 1) }}%</strong></div>
            <div class="rp-staff-progress" role="progressbar" aria-label="Occupancy rate" aria-valuenow="{{ $charts['occupancy_rate'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $charts['occupancy_rate'] }}%"></span></div>
        </section>

        <section class="rp-staff-panel" aria-labelledby="admin-activity-title">
            <div class="rp-staff-panel-head">
                <div>
                    <h2 id="admin-activity-title">Recent reservations</h2>
                    <p>Latest booking activity</p>
                </div>
            </div>
            <ol class="rp-staff-activity-list">
                @forelse($recentBookings as $booking)
                    <li>
                        <span class="rp-staff-activity-icon" aria-hidden="true"><i class="bi bi-calendar-check"></i></span>
                        <div class="rp-staff-activity-copy">
                            <strong>{{ $booking->guest_name }}</strong>
                            <span>{{ $booking->accommodation->name ?? 'Accommodation' }} · {{ ucfirst(str_replace('_', ' ', $booking->status->value)) }}</span>
                        </div>
                        <time datetime="{{ $booking->created_at->toIso8601String() }}">{{ $booking->created_at->diffForHumans() }}</time>
                    </li>
                @empty
                    <li class="rp-staff-empty">No reservations yet.</li>
                @endforelse
            </ol>
        </section>
    </div>

    @if($priorityReports->isNotEmpty())
        <section class="rp-staff-panel" aria-labelledby="admin-reports-title">
            <div class="rp-staff-panel-head">
                <div>
                    <h2 id="admin-reports-title">Reports needing attention</h2>
                    <p>Most recent pending incident reports</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="rp-staff-text-link">All reports <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
            <div class="rp-staff-queue">
                @foreach($priorityReports as $report)
                    <a href="{{ route('admin.reports.show', $report) }}" class="rp-staff-queue-item">
                        <span><strong>{{ $report->title }}</strong><small>{{ $report->report_number }} · {{ $report->location ?: 'Location not specified' }}</small></span>
                        <span class="rp-staff-pill">Pending</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const adminChartCanvas = document.getElementById('adminReservationsChart');
if (adminChartCanvas && window.Chart) {
    const context = adminChartCanvas.getContext('2d');
    const fill = context.createLinearGradient(0, 0, 0, 260);
    fill.addColorStop(0, 'rgba(31, 75, 93, 0.22)');
    fill.addColorStop(1, 'rgba(31, 75, 93, 0.01)');
    new Chart(context, {
        type: 'line',
        data: { labels: @json($charts['months']), datasets: [{ label: 'Reservations', data: @json($charts['reservations']), borderColor: '#1f4b5d', backgroundColor: fill, fill: true, tension: .35, borderWidth: 2, pointRadius: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
}
</script>
@endpush
