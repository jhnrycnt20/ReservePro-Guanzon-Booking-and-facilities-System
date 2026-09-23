@extends('layouts.dashboard')

@section('title', 'Incident Reports')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Incident Reports')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'status',
            'label' => 'Status',
            'empty' => 'Open queue',
            'value' => request('status'),
            'options' => [
                'verified' => 'Verified',
                'in_progress' => 'In progress',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ],
        ],
    ],
    'searchPlaceholder' => 'Report Number, Title, Location, or Guest',
    'clearUrl' => route('front_desk.incidents.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 14%;">Report #</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 22%;">Title</th>
                    <th style="width: 14%;">Location</th>
                    <th style="width: 14%;">Guest</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 16%;"></th>
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
                        <td><x-status-badge :status="$report->status" plain /></td>
                        <td class="text-end">
                            <a href="{{ route('front_desk.incidents.show', $report) }}" class="btn btn-sm btn-rp-soft">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No incident reports awaiting resolution.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links')) {{ $reports->withQueryString()->links() }} @endif
</div>
@endsection
