@extends('layouts.dashboard')

@section('title', 'Incident Investigations')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Incident Reports')
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
    'searchPlaceholder' => 'Report Number, Title, Location, or Guest',
    'clearUrl' => route('security.incidents.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 16%;">Report #</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 20%;">Title</th>
                    <th style="width: 14%;">Location</th>
                    <th style="width: 14%;">Guest</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 14%;"></th>
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
                            <button type="button" class="btn btn-sm btn-rp-primary" data-bs-toggle="modal" data-bs-target="#rpSecurityReportModal{{ $report->id }}">View</button>
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

@foreach($modalReports as $report)
    @include('security.incidents.detail-modal', ['report' => $report])
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rp-security-report-modal]').forEach((reportModal) => {
        const outcomeForm = reportModal.querySelector('[data-rp-outcome-form]');
        const outcome = outcomeForm?.querySelector('[data-rp-report-outcome]');
        const outcomeNotes = outcomeForm?.querySelector('[data-rp-outcome-notes]');
        const outcomeEvidence = outcomeForm?.querySelector('[data-rp-outcome-evidence]');
        const outcomeUpload = outcomeEvidence?.querySelector('input[type="file"]');
        const outcomeSubmit = outcomeForm?.querySelector('[data-rp-outcome-submit] span');
        const syncOutcome = () => {
            if (!outcomeForm || !outcome || !outcomeNotes) return;
            const isInvalid = outcome.value === 'invalid';
            outcomeForm.action = isInvalid ? outcomeForm.dataset.invalidAction : outcomeForm.dataset.verifyAction;
            outcomeNotes.name = isInvalid ? 'invalid_reason' : 'investigation_notes';
            outcomeNotes.placeholder = isInvalid ? 'Why is this report invalid?' : 'What did you find on site?';
            outcomeEvidence?.classList.toggle('d-none', isInvalid);
            if (outcomeUpload) outcomeUpload.disabled = isInvalid;
            if (outcomeSubmit) outcomeSubmit.textContent = 'Submit';
        };
        outcome?.addEventListener('change', syncOutcome);
        syncOutcome();

        const toggle = reportModal.querySelector('.rp-report-action-toggle');
        toggle?.querySelectorAll('[data-bs-toggle="tab"]').forEach((button) => {
            button.addEventListener('shown.bs.tab', () => {
                toggle.querySelectorAll('.rp-report-action-btn').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
            });
        });

        reportModal.querySelectorAll('[data-rp-notes-count]').forEach((notes) => {
            const notesValue = notes.closest('.rp-report-notes-wrap')?.querySelector('[data-rp-notes-count-value]');
            const syncCount = () => {
                if (notesValue) notesValue.textContent = String((notes.value || '').length);
            };
            notes.addEventListener('input', syncCount);
            syncCount();
        });

        reportModal.querySelectorAll('.rp-report-upload-input').forEach((upload) => {
            const uploadLabel = upload.closest('.rp-report-upload')?.querySelector('[data-rp-upload-label]');
            upload.addEventListener('change', () => {
                if (uploadLabel) uploadLabel.textContent = upload.files?.[0]?.name || 'Choose File or drag and drop';
            });
        });
    });

    document.querySelectorAll('[data-rp-security-photo-modal]').forEach((photoModal) => {
        photoModal.addEventListener('hidden.bs.modal', () => {
            const reportModal = document.getElementById(photoModal.dataset.rpReturnModal);
            if (reportModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(reportModal).show();
            }
        });
    });

    const selectedReportId = @json($selectedReportId ?: null);
    if (!selectedReportId || !window.bootstrap?.Modal) return;

    const modal = document.getElementById(`rpSecurityReportModal${selectedReportId}`);
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
