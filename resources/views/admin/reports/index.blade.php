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
<div class="rp-card rp-admin-list-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--reports">
            <thead>
                <tr>
                    <th scope="col" style="width: 16%;">Report ID</th>
                    <th scope="col" style="width: 12%;">Type</th>
                    <th scope="col" style="width: 22%;">Title</th>
                    <th scope="col" style="width: 14%;">Guest</th>
                    <th scope="col" style="width: 12%;">Status</th>
                    <th scope="col" style="width: 12%;">Submitted</th>
                    <th scope="col" class="rp-admin-list-actions-heading" style="width: 12%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td class="rp-admin-list-primary rp-admin-list-mono">{{ $report->report_number }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</td>
                        <td class="rp-admin-list-wrap">{{ $report->title }}</td>
                        <td>{{ $report->guest?->user?->name ?? '—' }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td class="rp-admin-list-mono">{{ $report->created_at?->format('M d, Y') }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpAdminReportModal{{ $report->id }}">View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links')) {{ $reports->links() }} @endif
</div>
@foreach($modalReports as $report)
    @include('admin.reports.detail-modal', ['report' => $report])
@endforeach
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-rp-report-photo-modal]').forEach((photoModal) => {
                photoModal.addEventListener('hidden.bs.modal', () => {
                    const reportModal = document.getElementById(photoModal.dataset.rpReturnModal);
                    if (reportModal && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(reportModal).show();
                    }
                });
            });
        });
    </script>
@endpush

@if($selectedReportId && $modalReports->contains('id', $selectedReportId))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById(@json('rpAdminReportModal'.$selectedReportId));
                if (modal && window.bootstrap?.Modal) {
                    let openingPhoto = false;
                    modal.addEventListener('click', (event) => {
                        if (event.target.closest('[data-bs-toggle="modal"]')) {
                            openingPhoto = true;
                        }
                    });
                    modal.addEventListener('hidden.bs.modal', () => {
                        if (openingPhoto) {
                            openingPhoto = false;
                            return;
                        }
                        const url = new URL(window.location.href);
                        url.searchParams.delete('report');
                        window.history.replaceState(window.history.state, '', url);
                    });
                    window.bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif
