@extends('layouts.dashboard')

@section('title', 'Analytics')
@section('theme', 'admin')
@section('role_label', 'Admin')
@section('page_title', 'Analytics')
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
const makeBars = (id, label, data, currency = false) => {
    const canvas = document.getElementById(id);
    if (!canvas || !window.Chart) return;
    new Chart(canvas, {
        type: 'bar',
        data: { labels: months, datasets: [{ label, data, backgroundColor: '#408c80', borderRadius: 5, maxBarThickness: 42 }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ({ parsed }) => currency ? `₱${parsed.y.toLocaleString()}` : `${parsed.y.toLocaleString()} reservations` } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#607681' }, border: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#607681', precision: currency ? undefined : 0, callback: value => currency ? `₱${Number(value).toLocaleString()}` : value },
                    grid: { color: '#e5eeeb' }, border: { display: false }
                }
            }
        }
    });
};

makeBars('reservationsChart', 'Reservations', reservations);
makeBars('revenueChart', 'Verified revenue', revenue, true);
</script>
@endpush
