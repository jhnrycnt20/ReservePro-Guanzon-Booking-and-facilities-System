@php
    $reportType = $report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type;
    $reportTypeLabel = $reportType ? str_replace('_', ' ', ucfirst($reportType)) : 'Report';
    $status = $report->status instanceof \BackedEnum ? $report->status->value : (string) $report->status;
    $statusLabel = str_replace('_', ' ', ucwords($status, '_'));
    $guestName = $report->guest?->user?->name ?? '—';
    $bookingLabel = $report->booking
        ? ($report->booking->short_number ?? $report->booking->booking_number)
        : null;
    $issueTitle = $report->title ?: \Illuminate\Support\Str::limit((string) $report->description, 80);
@endphp

<div class="modal fade" id="rpSecurityReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpSecurityReportModal{{ $report->id }}Label" aria-hidden="true" data-rp-security-reviewed-report-modal>
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable rp-security-reviewed-report-dialog">
        <div class="modal-content rp-admin-modal rp-admin-report-modal rp-security-reviewed-report-modal">
            <div class="modal-header">
                <div class="rp-admin-report-header-copy rp-security-report-heading">
                    <h2 class="modal-title" id="rpSecurityReportModal{{ $report->id }}Label">Reports</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="rp-reviewed-report-detail-grid">
                    <section class="rp-reviewed-report-main">
                        <div class="rp-reviewed-report-block">
                            <h4 class="rp-reviewed-report-label">Description</h4>
                            <p class="rp-reviewed-report-copy mb-0">{{ $report->description }}</p>
                        </div>

                        <div class="rp-reviewed-report-block mb-0">
                            <h4 class="rp-reviewed-report-label">{{ $status === 'invalid' ? 'Invalid reason' : 'Investigation result' }}</h4>
                            <div class="rp-reviewed-report-result">
                                <p class="rp-reviewed-report-copy mb-0">{{ $status === 'invalid' ? ($report->invalid_reason ?? '—') : ($report->investigation_notes ?? '—') }}</p>
                            </div>

                            @if($report->investigation_photo)
                                <div class="rp-reviewed-report-photo-wrap mt-3">
                                    <button type="button" class="rp-reviewed-report-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpSecurityInvestigationPhotoModal{{ $report->id }}" aria-label="View investigation photo">
                                        <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-reviewed-report-photo" alt="Investigation photo">
                                    </button>
                                    <button type="button" class="rp-reviewed-report-photo-expand" data-bs-toggle="modal" data-bs-target="#rpSecurityInvestigationPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full investigation photo">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($report->photo)
                            <div class="rp-reviewed-report-block mb-0 mt-4">
                                <h4 class="rp-reviewed-report-label">Guest photo</h4>
                                <div class="rp-reviewed-report-photo-wrap">
                                    <button type="button" class="rp-reviewed-report-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpSecurityGuestPhotoModal{{ $report->id }}" aria-label="View guest photo">
                                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-reviewed-report-photo" alt="Guest report photo">
                                    </button>
                                    <button type="button" class="rp-reviewed-report-photo-expand" data-bs-toggle="modal" data-bs-target="#rpSecurityGuestPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full guest photo">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </section>

                    <aside class="rp-reviewed-report-information">
                        <h4 class="rp-reviewed-report-label">Report information</h4>
                        <div class="rp-reviewed-report-meta">
                            <div><span>Report ID</span><strong>{{ $report->report_number }}</strong></div>
                            <div><span>Report type</span><strong>{{ $reportTypeLabel }}</strong></div>
                            <div><span>Location</span><strong>{{ $report->location ?: '—' }}</strong></div>
                            <div><span>Status</span><strong>{{ $statusLabel }}</strong></div>
                            <div><span>Guest</span><strong>{{ $guestName }}</strong></div>
                            @if($bookingLabel)
                                <div><span>Booking</span><strong>{{ $bookingLabel }}</strong></div>
                            @endif
                            <div><span>Reported</span><strong>{{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</strong></div>
                            @if($report->securityGuard)
                                <div><span>Reviewed by</span><strong>{{ $report->securityGuard->name }}</strong></div>
                            @endif
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

@if($report->photo)
<div class="modal fade" id="rpSecurityGuestPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpSecurityGuestPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-security-photo-modal data-rp-return-modal="rpSecurityReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpSecurityGuestPhotoModal{{ $report->id }}Label">Guest photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2"><img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-photo-modal-img" alt="Guest report photo full size"></div>
        </div>
    </div>
</div>
@endif

@if($report->investigation_photo)
<div class="modal fade" id="rpSecurityInvestigationPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpSecurityInvestigationPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-security-photo-modal data-rp-return-modal="rpSecurityReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpSecurityInvestigationPhotoModal{{ $report->id }}Label">Investigation photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2"><img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-photo-modal-img" alt="Investigation photo full size"></div>
        </div>
    </div>
</div>
@endif
