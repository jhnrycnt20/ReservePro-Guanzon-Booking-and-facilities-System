@extends('layouts.dashboard')

@section('title', 'Incident Reports')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Incident Reports')
@section('page_subtitle', 'Resolve verified reports and track progress')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
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
                            <a href="{{ route('front_desk.incidents.show', $report) }}" class="btn btn-sm btn-rp-primary">Open</a>
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
