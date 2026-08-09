@extends('layouts.dashboard')

@section('title', 'My Reports')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'My Reports')
@section('page_subtitle', 'Track incident and amenity report status')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('guest.incidents.create') }}" class="btn btn-rp-primary">New Report</a>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Report #</th><th>Type</th><th>Title</th><th>Location</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->location }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td><a href="{{ route('guest.incidents.show', $report) }}" class="btn btn-sm btn-outline-secondary">Track</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No reports submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
