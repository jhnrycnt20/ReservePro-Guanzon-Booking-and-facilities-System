@extends('layouts.dashboard')

@section('title', 'Incident Investigations')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Incident Reports')
@section('page_subtitle', 'Review and investigate guest reports')
@section('sidebar')
    @include('partials.sidebar-security')
@endsection

@section('content')
<div class="rp-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All open</option>
                @foreach(['pending', 'verified', 'invalid', 'in_progress', 'resolved', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Report # or title">
        </div>
        <div class="col-md-4">
            <button class="btn btn-rp-primary w-100">Filter</button>
        </div>
    </form>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Report #</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Guest</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->location }}</td>
                        <td>{{ $report->guest?->user?->name ?? '—' }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td>
                            <a href="{{ route('security.incidents.show', $report) }}" class="btn btn-sm btn-rp-primary">Investigate</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links')) {{ $reports->withQueryString()->links() }} @endif
</div>
@endsection
