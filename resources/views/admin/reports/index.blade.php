@extends('layouts.dashboard')

@section('title', 'Incident Monitoring')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Incident Reports')
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
    'searchPlaceholder' => 'Report Number, Title, Location, or Guest',
    'clearUrl' => route('admin.reports.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 16%;">Report ID</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 24%;">Title</th>
                    <th style="width: 14%;">Guest</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;">Submitted</th>
                    <th style="width: 12%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->guest?->user?->name ?? '—' }}</td>
                        <td><x-status-badge :status="$report->status" plain /></td>
                        <td>{{ $report->created_at?->format('M d, Y') }}</td>
                        <td class="text-end">
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
