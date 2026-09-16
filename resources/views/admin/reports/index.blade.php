@extends('layouts.dashboard')

@section('title', 'Incident Monitoring')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Incident Reports')
@section('page_subtitle', 'Read-only monitoring of all reports')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'status',
            'label' => 'Status',
            'empty' => 'All',
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
    'clearUrl' => route('admin.reports.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Report #</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Guest</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->guest?->user?->name ?? '—' }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td>{{ $report->created_at?->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-rp-soft">View</a>
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
