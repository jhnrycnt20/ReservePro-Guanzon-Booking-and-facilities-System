@extends('layouts.dashboard')

@section('title', 'Incident Reports')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Incident Reports')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="text-muted small">
        Default view shows unresolved reports only
        @if(($unresolvedCount ?? 0) > 0)
            · <strong>{{ $unresolvedCount }}</strong> still need action
        @endif
    </div>
</div>

@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'status',
            'label' => 'Status',
            'empty' => 'Unresolved only',
            'value' => request('status'),
            'options' => [
                'verified' => 'Verified (ready for FD)',
                'in_progress' => 'Being processed',
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
                            <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpFdReportModal{{ $report->id }}">View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No unresolved incident reports right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links')) {{ $reports->withQueryString()->links() }} @endif
</div>

@foreach($modalReports as $report)
    @include('front_desk.incidents.detail-modal', ['report' => $report])
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rp-notes-count]').forEach((notes) => {
        const notesValue = notes.closest('.rp-report-notes-wrap')?.querySelector('[data-rp-notes-count-value]');
        const syncCount = () => {
            if (notesValue) notesValue.textContent = String((notes.value || '').length);
        };
        notes.addEventListener('input', syncCount);
        syncCount();
    });

    document.querySelectorAll('[data-rp-fd-photo-modal]').forEach((photoModal) => {
        photoModal.addEventListener('hidden.bs.modal', () => {
            const reportModal = document.getElementById(photoModal.dataset.rpReturnModal);
            if (reportModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(reportModal).show();
            }
        });
    });

    const selectedReportId = @json($selectedReportId ?: null);
    if (!selectedReportId || !window.bootstrap?.Modal) return;

    const modal = document.getElementById(`rpFdReportModal${selectedReportId}`);
    if (!modal) return;

    let openingPhoto = false;
    modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-bs-toggle="modal"]')) openingPhoto = true;
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
});
</script>
@endpush
