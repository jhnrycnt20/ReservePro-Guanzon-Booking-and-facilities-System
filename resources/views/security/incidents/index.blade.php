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
@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'status',
            'label' => 'Status',
            'empty' => 'All open',
            'value' => request('status'),
            'options' => [
                'pending' => 'Pending',
                'verified' => 'Verified',
                'invalid' => 'Invalid',
                'in_progress' => 'In progress',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ],
        ],
    ],
    'searchPlaceholder' => 'Report #, title, location, or guest',
    'clearUrl' => route('security.incidents.index'),
])
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
