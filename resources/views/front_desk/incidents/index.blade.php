@extends('layouts.dashboard')

@section('title', 'Incident Reports')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Incident Reports')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-incident-toolbar">
    <form method="GET" class="rp-incident-search-form">
        @if(filled(request('status')))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @if(filled(request('from')))
            <input type="hidden" name="from" value="{{ request('from') }}">
        @endif
        @if(filled(request('to')))
            <input type="hidden" name="to" value="{{ request('to') }}">
        @endif
        <div class="rp-resv-search">
            <label for="rpIncidentSearch" class="visually-hidden">Search incident reports</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                id="rpIncidentSearch"
                type="search"
                name="q"
                value="{{ request('q') }}"
                class="form-control"
                placeholder="Report Number, Title, Location, or Guest"
                autocomplete="off"
            >
        </div>
    </form>
    <div class="rp-incident-date" data-rp-incident-date>
        <button type="button" class="rp-incident-date-btn" data-rp-incident-date-toggle aria-expanded="false" aria-controls="rpIncidentDatePanel">
            <span><i class="bi bi-calendar3" aria-hidden="true"></i><span>Date range</span></span>
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="rp-resv-date-panel" id="rpIncidentDatePanel" data-rp-incident-date-panel hidden>
            <form method="GET">
                @if(filled(request('q')))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                @if(filled(request('status')))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="rp-resv-date-panel-title">Filter by report date</div>
                <label class="form-label" for="rpIncidentDateFrom">From</label>
                <input id="rpIncidentDateFrom" type="date" name="from" value="{{ request('from') }}" class="form-control mb-2">
                <label class="form-label" for="rpIncidentDateTo">To</label>
                <input id="rpIncidentDateTo" type="date" name="to" value="{{ request('to') }}" class="form-control mb-3">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-rp-primary btn-sm">Apply dates</button>
                    <a href="{{ route('front_desk.incidents.index', array_filter(['q' => request('q'), 'status' => request('status')])) }}" class="btn btn-rp-soft btn-sm">Clear dates</a>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="rp-report-table-card">
    <div class="table-responsive">
        <table class="table align-middle rp-report-table mb-0">
            <thead>
                <tr>
                    <th style="width: 14%;">Report ID</th>
                    <th style="width: 14%;">Guest</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 22%;">Title</th>
                    <th style="width: 14%;">Location</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 16%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td><span class="rp-report-id">{{ $report->report_number }}</span></td>
                        <td><span class="rp-report-table-copy">{{ $report->guest?->user?->name ?? '—' }}</span></td>
                        <td><span class="rp-report-table-copy">{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</span></td>
                        <td><span class="rp-report-table-copy">{{ $report->title }}</span></td>
                        <td><span class="rp-report-table-copy">{{ $report->location }}</span></td>
                        <td><x-status-badge :status="$report->status" plain /></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpFdReportModal{{ $report->id }}">View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No unresolved incident reports right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links') && $reports->total() > 0)
        <div class="rp-resv-footer">
            <div class="rp-resv-footer-meta">
                Showing {{ $reports->firstItem() }}-{{ $reports->lastItem() }} of {{ $reports->total() }} reports
            </div>
            <div class="rp-resv-pagination">
                @if($reports->onFirstPage())
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $reports->withQueryString()->previousPageUrl() }}" class="rp-resv-page" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                @endif
                @for($page = 1; $page <= $reports->lastPage(); $page++)
                    @if($page === $reports->currentPage())
                        <span class="rp-resv-page is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $reports->withQueryString()->url($page) }}" class="rp-resv-page" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endfor
                @if($reports->hasMorePages())
                    <a href="{{ $reports->withQueryString()->nextPageUrl() }}" class="rp-resv-page" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>
    @endif
</div>

@foreach($modalReports as $report)
    @include('front_desk.incidents.detail-modal', ['report' => $report])
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateWrap = document.querySelector('[data-rp-incident-date]');
    const dateToggle = dateWrap?.querySelector('[data-rp-incident-date-toggle]');
    const datePanel = dateWrap?.querySelector('[data-rp-incident-date-panel]');
    if (dateWrap && dateToggle && datePanel) {
        dateToggle.addEventListener('click', () => {
            const isOpen = dateToggle.getAttribute('aria-expanded') === 'true';
            dateToggle.setAttribute('aria-expanded', String(!isOpen));
            datePanel.hidden = isOpen;
        });
        document.addEventListener('click', (event) => {
            if (!dateWrap.contains(event.target)) {
                dateToggle.setAttribute('aria-expanded', 'false');
                datePanel.hidden = true;
            }
        });
    }

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
