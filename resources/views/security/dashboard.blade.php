@extends('layouts.dashboard')

@section('title', 'Security Dashboard')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Welcome back, ' . strtok(auth()->user()->name, ' '))
@section('page_subtitle', 'Track new reports and investigation progress.')
@section('sidebar')
    @include('partials.sidebar-security')
@endsection

@section('content')
<div class="rp-staff-dashboard">
    <div class="rp-staff-stat-grid">
        <x-staff-stat label="Pending reports" :value="$stats['pending']" icon="bi-hourglass-split" hint="Waiting for investigation" />
        <x-staff-stat label="Verified" :value="$stats['verified']" icon="bi-patch-check" hint="Confirmed reports" />
        <x-staff-stat label="In progress" :value="$stats['in_progress']" icon="bi-shield-check" hint="Under investigation" />
        <x-staff-stat label="Invalid" :value="$stats['invalid']" icon="bi-x-circle" hint="Closed as invalid" />
    </div>

    <section class="rp-staff-panel rp-staff-chart-panel" aria-labelledby="security-chart-title">
        <div class="rp-staff-panel-head">
            <div>
                <h2 id="security-chart-title">Report activity</h2>
                <p>Reports filed over seven days, grouped by current status</p>
            </div>
            <a href="{{ route('security.incidents.index') }}" class="rp-staff-text-link">All reports <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="rp-staff-chart-wrap"><canvas id="securityReportsChart" aria-label="Daily pending and reviewed reports chart" role="img"></canvas></div>
    </section>

    <section class="rp-staff-panel" aria-labelledby="security-pending-title">
        <div class="rp-staff-panel-head">
            <div>
                <h2 id="security-pending-title">New pending reports</h2>
                <p>Open an incident to review and record findings</p>
            </div>
            <a href="{{ route('security.incidents.index', ['status' => 'pending']) }}" class="rp-staff-text-link">View queue <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="rp-staff-queue">
            @forelse($pendingReports as $report)
                <a href="{{ route('security.incidents.show', $report) }}" class="rp-staff-queue-item">
                    <span class="rp-staff-activity-icon" aria-hidden="true"><i class="bi bi-clipboard2-pulse"></i></span>
                    <span class="rp-staff-queue-copy">
                        <strong>{{ $report->title }}</strong>
                        <small>{{ $report->report_number }} · {{ ucfirst(str_replace('_', ' ', $report->report_type->value)) }} · {{ $report->location ?: 'Location not specified' }}</small>
                    </span>
                    <span class="rp-staff-pill">Pending</span>
                </a>
            @empty
                <div class="rp-staff-empty">No pending reports. The investigation queue is clear.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const securityChartCanvas = document.getElementById('securityReportsChart');
if (securityChartCanvas && window.Chart) {
    const context = securityChartCanvas.getContext('2d');
    const fill = context.createLinearGradient(0, 0, 0, 260);
    fill.addColorStop(0, 'rgba(31, 75, 93, 0.16)');
    fill.addColorStop(1, 'rgba(31, 75, 93, 0.01)');
    new Chart(context, {
        type: 'line',
        data: { labels: @json($chart['days']), datasets: [
            { label: 'Pending', data: @json($chart['pending']), borderColor: '#1f4b5d', backgroundColor: fill, fill: true, tension: .35, borderWidth: 2, pointRadius: 3 },
            { label: 'Reviewed', data: @json($chart['reviewed']), borderColor: '#72b9ac', borderDash: [5, 5], fill: false, tension: .35, borderWidth: 2, pointRadius: 3 }
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
}
</script>
@endpush
