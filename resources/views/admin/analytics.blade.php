@extends('layouts.dashboard')

@section('title', 'Analytics')
@section('theme', 'admin')
@section('role_label', 'Admin')
@section('page_title', 'Analytics')
@section('page_subtitle', 'Charts and trends across reservations, revenue, and incidents')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@include('partials.admin-dashboard-charts')
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

makeLine('reservationsChart', 'Reservations', reservations, '#16a34a');
makeLine('revenueChart', 'Revenue', revenue, '#1f7a4d');

new Chart(document.getElementById('incidentTypeChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(incidentTypes),
        datasets: [{ data: Object.values(incidentTypes), backgroundColor: ['#16a34a','#1f7a4d','#86efac','#14532d'] }]
    }
});

new Chart(document.getElementById('incidentStatusChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(incidentStatuses),
        datasets: [{ data: Object.values(incidentStatuses), backgroundColor: '#16a34a' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
