@extends('layouts.dashboard')

@section('title', 'Security Dashboard')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Security Dashboard')
@section('page_subtitle', 'Investigate guest reports and forward verified cases')
@section('sidebar')
    @include('partials.sidebar-security')
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Pending</div><div class="value">{{ $stats['pending'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Verified</div><div class="value">{{ $stats['verified'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">Invalid</div><div class="value">{{ $stats['invalid'] ?? 0 }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="rp-stat"><div class="label">In Progress</div><div class="value">{{ $stats['in_progress'] ?? 0 }}</div></div></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('security.incidents.index', ['status' => 'pending']) }}" class="btn btn-rp-primary">View Pending Reports</a>
    <a href="{{ route('security.incidents.index') }}" class="btn btn-rp-soft">All Investigations</a>
</div>

<div class="rp-card">
    <h2 class="h5 mb-3">New Pending Reports</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Report #</th><th>Type</th><th>Location</th><th>Guest</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($pendingReports ?? [] as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td>{{ $report->location }}</td>
                        <td>{{ $report->guest->user->name ?? 'Guest' }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td><a href="{{ route('security.incidents.show', $report) }}" class="btn btn-sm btn-rp-primary">Investigate</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No pending reports.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
