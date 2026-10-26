<div class="modal fade" id="rpAdminReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpAdminReportModal{{ $report->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rp-admin-modal rp-admin-report-modal">
            <div class="modal-header">
                <div class="rp-admin-report-header-copy">
                    <h2 class="modal-title" id="rpAdminReportModal{{ $report->id }}Label">Report details</h2>
                    <span class="rp-admin-report-number">{{ $report->report_number }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $reportType = $report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type;
                    $status = $report->status instanceof \BackedEnum ? $report->status->value : (string) $report->status;
                    $statusLabel = str_replace('_', ' ', ucwords($status, '_'));
                    $heading = match ($reportType) {
                        'broken_amenity' => 'Broken Amenity Report',
                        'complaint' => 'Complaint Report',
                        'maintenance' => 'Maintenance Report',
                        'incident' => 'Incident Report',
                        default => 'Guest Report',
                    };
                    $guestName = $report->guest?->user?->name ?? '—';
                    $bookingLabel = $report->booking
                        ? ($report->booking->short_number ?? $report->booking->booking_number)
                        : null;
                    $statusIcon = match ($status) {
                        'verified' => 'bi-shield-check',
                        'in_progress' => 'bi-arrow-repeat',
                        'resolved', 'closed' => 'bi-check-circle',
                        'invalid' => 'bi-x-circle',
                        default => 'bi-clock',
                    };
                    $issueTitle = $report->title ?: \Illuminate\Support\Str::limit((string) $report->description, 80);
                    $actionLabels = [
                        'repair_broken_amenity' => 'Repair broken amenity',
                        'assist_guest' => 'Assist guest',
                        'contact_maintenance' => 'Contact maintenance',
                        'contact_security' => 'Contact security',
                        'other' => 'Other',
                    ];
                @endphp

                <div class="rp-report-detail-hero">
                    <div class="rp-report-detail-hero-main">
                        <div>
                            <h3 class="rp-report-detail-title">{{ $issueTitle }}</h3>
                            <div class="rp-report-detail-tags">
                                <span class="rp-report-chip is-type">{{ $heading }}</span>
                                @if($report->location)
                                    <span class="rp-report-chip is-location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $report->location }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="rp-report-detail-status is-{{ $status }}">
                        <i class="bi {{ $statusIcon }}" aria-hidden="true"></i>
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="rp-report-detail-grid">
                    <section class="rp-report-detail-card">
                        <div class="rp-report-detail-block">
                            <h4 class="rp-report-detail-label">Description</h4>
                            <p class="rp-report-detail-copy mb-0">{{ $report->description }}</p>
                        </div>

                        @if($report->photo)
                            <div class="rp-report-detail-block">
                                <h4 class="rp-report-detail-label">Guest photo</h4>
                                <div class="rp-report-detail-photo-wrap">
                                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpAdminGuestPhotoModal{{ $report->id }}" aria-label="View guest photo">
                                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                                    </button>
                                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpAdminGuestPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full guest photo">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($report->investigation_notes || $report->investigation_photo)
                            <div class="rp-report-detail-block">
                                <h4 class="rp-report-detail-label">Security investigation</h4>
                                <div class="rp-report-note-panel">
                                    <p class="rp-report-detail-copy mb-0">{{ $report->investigation_notes ?? '—' }}</p>
                                </div>
                                @if($report->investigation_photo)
                                    <div class="rp-report-detail-photo-wrap mt-3">
                                        <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpAdminInvestigationPhotoModal{{ $report->id }}" aria-label="View investigation photo">
                                            <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo">
                                        </button>
                                        <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpAdminInvestigationPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full investigation photo">
                                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($report->resolution_notes || $report->resolution_action || $report->invalid_reason)
                            <div class="rp-report-detail-block mb-0">
                                <h4 class="rp-report-detail-label">{{ $status === 'invalid' ? 'Invalid reason' : 'Resolution' }}</h4>
                                @if($report->resolution_action && $status !== 'invalid')
                                    <div class="rp-report-detail-meta-value mb-2">
                                        <i class="bi bi-wrench" aria-hidden="true"></i>
                                        {{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}
                                    </div>
                                @endif
                                <div class="rp-report-note-panel">
                                    <p class="rp-report-detail-copy mb-0">
                                        {{ $status === 'invalid' ? ($report->invalid_reason ?? '—') : ($report->resolution_notes ?? '—') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>

                    <aside class="rp-report-detail-card rp-report-review-card">
                        <div class="rp-report-review-head">
                            <h3 class="rp-report-review-title">People &amp; timing</h3>
                        </div>

                        <div class="rp-report-detail-meta">
                            <div>
                                <div class="rp-report-detail-label">Guest</div>
                                <div class="rp-report-detail-meta-value">{{ $guestName }}</div>
                            </div>
                            @if($bookingLabel)
                                <div>
                                    <div class="rp-report-detail-label">Booking</div>
                                    <div class="rp-report-detail-meta-value">{{ $bookingLabel }}</div>
                                </div>
                            @endif
                            <div>
                                <div class="rp-report-detail-label">Security</div>
                                <div class="rp-report-detail-meta-value">{{ $report->securityGuard->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="rp-report-detail-label">Front desk</div>
                                <div class="rp-report-detail-meta-value">{{ $report->frontDeskStaff->name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="rp-report-detail-label">Submitted</div>
                                <div class="rp-report-detail-meta-value">{{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
                            </div>
                            @if($report->resolved_at)
                                <div>
                                    <div class="rp-report-detail-label">Resolved</div>
                                    <div class="rp-report-detail-meta-value">{{ $report->resolved_at->format('M j, Y · g:i A') }}</div>
                                </div>
                            @endif
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

@if($report->photo)
<div class="modal fade" id="rpAdminGuestPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpAdminGuestPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-report-photo-modal data-rp-return-modal="rpAdminReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpAdminGuestPhotoModal{{ $report->id }}Label">Guest photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2">
                <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-photo-modal-img" alt="Guest report photo full size">
            </div>
        </div>
    </div>
</div>
@endif

@if($report->investigation_photo)
<div class="modal fade" id="rpAdminInvestigationPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpAdminInvestigationPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-report-photo-modal data-rp-return-modal="rpAdminReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpAdminInvestigationPhotoModal{{ $report->id }}Label">Investigation photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2">
                <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-photo-modal-img" alt="Investigation photo full size">
            </div>
        </div>
    </div>
</div>
@endif
