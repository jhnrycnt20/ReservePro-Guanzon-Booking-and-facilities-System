@extends('layouts.dashboard')

@section('title', 'Incident Monitoring')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Incident Reports')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-admin-list-toolbar rp-admin-list-toolbar--search-only">
    <form method="GET" class="rp-admin-list-search" data-rp-live-filter>
        <div class="rp-resv-search">
            <label for="rpIncidentReportSearch" class="visually-hidden">Search incident reports</label>
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                id="rpIncidentReportSearch"
                type="search"
                name="q"
                value="{{ request('q') }}"
                class="form-control"
                placeholder="Search by report number, title, location, or guest..."
                data-rp-live-filter-q
                autocomplete="off"
            >
        </div>
    </form>
</div>
<div class="rp-card rp-admin-list-card rp-admin-table-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--reports mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width: 16%;">Report ID</th>
                    <th scope="col" style="width: 12%;">Type</th>
                    <th scope="col" style="width: 22%;">Title</th>
                    <th scope="col" style="width: 14%;">Guest</th>
                    <th scope="col" style="width: 12%;">Status</th>
                    <th scope="col" style="width: 12%;">Submitted</th>
                    <th scope="col" class="rp-admin-list-actions-heading" style="width: 12%;"><span class="visually-hidden">Actions</span></th>
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
    @if(method_exists($reports, 'links') && $reports->total() > 0)
        <div class="rp-resv-footer">
            <div class="rp-resv-footer-meta">
                Showing {{ $reports->firstItem() }}-{{ $reports->lastItem() }} of {{ $reports->total() }} reports
            </div>
            <div class="rp-resv-pagination">
                @if($reports->onFirstPage())
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $reports->previousPageUrl() }}" class="rp-resv-page" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                @endif
                @for($page = 1; $page <= $reports->lastPage(); $page++)
                    @if($page === $reports->currentPage())
                        <span class="rp-resv-page is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $reports->url($page) }}" class="rp-resv-page" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endfor
                @if($reports->hasMorePages())
                    <a href="{{ $reports->nextPageUrl() }}" class="rp-resv-page" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>
    @endif
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
