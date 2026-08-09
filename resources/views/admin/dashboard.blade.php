@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')
@section('theme', 'admin')
@section('role_label', 'Admin / Owner')
@section('page_title', 'Admin Dashboard')
@section('page_subtitle', 'Monitor resort operations, revenue, and incidents')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Total Reservations</div><div class="value">{{ $stats['total_reservations'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Today</div><div class="value">{{ $stats['today_reservations'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Total Guests</div><div class="value">{{ $stats['total_guests'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Revenue</div><div class="value" style="font-size:1.3rem;">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Available</div><div class="value">{{ $stats['available'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Occupied</div><div class="value">{{ $stats['occupied'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending Reports</div><div class="value">{{ $stats['pending_reports'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Resolved</div><div class="value">{{ $stats['resolved_reports'] ?? 0 }}</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Reservations per Month</h2>
            <canvas id="reservationsChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Revenue per Month</h2>
            <canvas id="revenueChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Incident Reports by Type</h2>
            <canvas id="incidentTypeChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Report Status Distribution</h2>
            <canvas id="incidentStatusChart" height="180"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const months = @json($charts['months'] ?? []);
const reservations = @json($charts['reservations'] ?? []);
const revenue = @json($charts['revenue'] ?? []);
const incidentTypes = @json($charts['incident_types'] ?? []);
const incidentStatuses = @json($charts['incident_statuses'] ?? []);

const makeLine = (id, label, data, color) => new Chart(document.getElementById(id), {
    type: 'line',
    data: { labels: months, datasets: [{ label, data, borderColor: color, tension: .3, fill: false }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

makeLine('reservationsChart', 'Reservations', reservations, '#7c3aed');
makeLine('revenueChart', 'Revenue', revenue, '#2563eb');

new Chart(document.getElementById('incidentTypeChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(incidentTypes),
        datasets: [{ data: Object.values(incidentTypes), backgroundColor: ['#7c3aed','#ca8a04','#2563eb','#16a34a'] }]
    }
});

new Chart(document.getElementById('incidentStatusChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(incidentStatuses),
        datasets: [{ data: Object.values(incidentStatuses), backgroundColor: '#7c3aed' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
