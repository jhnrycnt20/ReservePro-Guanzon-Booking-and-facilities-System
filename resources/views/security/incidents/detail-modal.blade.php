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
    $statusIcon = match ($status) {
        'verified', 'in_progress' => 'bi-check2-circle',
        'resolved', 'closed' => 'bi-check-circle',
        'invalid' => 'bi-x-circle',
        default => 'bi-clock',
    };
    $isValidationTarget = (int) old('_modal') === $report->id;
    $selectedOutcome = $isValidationTarget ? old('outcome', 'verified') : 'verified';
@endphp

<div class="modal fade" id="rpSecurityReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpSecurityReportModal{{ $report->id }}Label" aria-hidden="true" data-rp-security-report-modal>
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable rp-security-report-dialog">
        <div class="modal-content rp-admin-modal rp-admin-report-modal rp-security-report-modal">
            <div class="modal-header">
                <div class="rp-admin-report-header-copy">
                    <h2 class="modal-title" id="rpSecurityReportModal{{ $report->id }}Label">{{ $issueTitle }}</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="rp-report-detail-hero visually-hidden">
                    <div class="rp-report-detail-hero-main">
                        <div>
                            <h3 class="rp-report-detail-title">{{ $issueTitle }}</h3>
                            <div class="rp-report-detail-tags">
                                <span class="rp-report-chip is-type"><i class="bi bi-tools" aria-hidden="true"></i> {{ $reportTypeLabel }}</span>
                                @if($report->location)
                                    <span class="rp-report-chip is-location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $report->location }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rp-report-detail-grid">
                    <section class="rp-report-detail-card">
                        <div class="rp-report-detail-block">
                            <h4 class="rp-report-detail-label">Description</h4>
                            <p class="rp-report-detail-copy mb-0">{{ $report->description }}</p>
                        </div>

                        <div class="rp-report-detail-block">
                            <h4 class="rp-report-detail-label">Report information</h4>
                            <div class="rp-report-detail-meta">
                                <div>
                                    <div class="rp-report-detail-label">Report ID</div>
                                    <div class="rp-report-detail-meta-value">{{ $report->report_number }}</div>
                                </div>
                                <div>
                                    <div class="rp-report-detail-label">Report type</div>
                                    <div class="rp-report-detail-meta-value">{{ $reportTypeLabel }}</div>
                                </div>
                                <div>
                                    <div class="rp-report-detail-label">Location</div>
                                    <div class="rp-report-detail-meta-value">{{ $report->location ?: '—' }}</div>
                                </div>
                                <div>
                                    <div class="rp-report-detail-label">Status</div>
                                    <div class="rp-report-detail-meta-value">{{ $statusLabel }}</div>
                                </div>
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
                                    <div class="rp-report-detail-label">Reported</div>
                                    <div class="rp-report-detail-meta-value">{{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
                                </div>
                                @if($report->securityGuard)
                                    <div>
                                        <div class="rp-report-detail-label">Reviewed by</div>
                                        <div class="rp-report-detail-meta-value">{{ $report->securityGuard->name }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($report->photo)
                            <div class="rp-report-detail-block">
                                <h4 class="rp-report-detail-label">Guest photo</h4>
                                <div class="rp-report-detail-photo-wrap">
                                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpSecurityGuestPhotoModal{{ $report->id }}" aria-label="View guest photo">
                                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                                    </button>
                                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpSecurityGuestPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full guest photo">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($status !== 'pending')
                            <div class="rp-report-detail-block mb-0">
                                <h4 class="rp-report-detail-label">{{ $status === 'invalid' ? 'Invalid reason' : 'Investigation result' }}</h4>
                                <div class="rp-report-note-panel">
                                    <p class="rp-report-detail-copy mb-0">{{ $status === 'invalid' ? ($report->invalid_reason ?? '—') : ($report->investigation_notes ?? '—') }}</p>
                                </div>

                                @if($report->investigation_photo)
                                    <div class="rp-report-detail-photo-wrap mt-3">
                                        <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpSecurityInvestigationPhotoModal{{ $report->id }}" aria-label="View investigation photo">
                                            <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo">
                                        </button>
                                        <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpSecurityInvestigationPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full investigation photo">
                                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </section>

                    <aside class="rp-report-detail-card rp-report-review-card">
                        @if($status === 'pending')
                            <div>
                                    <form
                                        method="POST"
                                        action="{{ $selectedOutcome === 'invalid' ? route('security.incidents.invalidate', $report) : route('security.incidents.verify', $report) }}"
                                        enctype="multipart/form-data"
                                        class="rp-report-review-form"
                                        data-rp-outcome-form
                                        data-verify-action="{{ route('security.incidents.verify', $report) }}"
                                        data-invalid-action="{{ route('security.incidents.invalidate', $report) }}"
                                    >
                                        @csrf
                                        <input type="hidden" name="_modal" value="{{ $report->id }}">
                                        <div class="mb-3">
                                            <label class="form-label" for="rpInvestigationNotes{{ $report->id }}">Investigation notes / findings</label>
                                            <div class="rp-report-notes-wrap">
                                                <textarea id="rpInvestigationNotes{{ $report->id }}" name="{{ $selectedOutcome === 'invalid' ? 'invalid_reason' : 'investigation_notes' }}" class="form-control" rows="5" maxlength="1000" placeholder="{{ $selectedOutcome === 'invalid' ? 'Why is this report invalid?' : 'What did you find on site?' }}" required data-rp-notes-count data-rp-outcome-notes>{{ $isValidationTarget ? old($selectedOutcome === 'invalid' ? 'invalid_reason' : 'investigation_notes') : '' }}</textarea>
                                                <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                                            </div>
                                            @if($isValidationTarget) @error('investigation_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror @endif
                                            @if($isValidationTarget) @error('invalid_reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror @endif
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="rpReportOutcome{{ $report->id }}">Outcome</label>
                                            <select id="rpReportOutcome{{ $report->id }}" name="outcome" class="form-select" data-rp-report-outcome>
                                                <option value="verified" @selected($selectedOutcome === 'verified')>Verified</option>
                                                <option value="invalid" @selected($selectedOutcome === 'invalid')>Invalid</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 {{ $selectedOutcome === 'invalid' ? 'd-none' : '' }}" data-rp-outcome-evidence>
                                            <label class="form-label">Evidence photo <span class="rp-report-optional">Optional</span></label>
                                            <label class="rp-report-upload">
                                                <input type="file" name="investigation_photo" accept="image/jpeg,image/png,image/webp" class="rp-report-upload-input">
                                                <span class="rp-report-upload-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-up"></i></span>
                                                <span class="rp-report-upload-hint">Choose or drop a JPG, PNG, or WEBP up to 5 MB</span>
                                            </label>
                                            @if($isValidationTarget) @error('investigation_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror @endif
                                        </div>
                                        <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit" data-rp-outcome-submit>
                                            <span>Submit</span>
                                        </button>
                                    </form>
                            </div>
                        @else
                            <div class="rp-report-review-head">
                                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi {{ $statusIcon }}"></i></span>
                                <div>
                                    <h3 class="rp-report-review-title">Investigation completed</h3>
                                    <p class="rp-report-review-sub mb-0">This report has already been reviewed.</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-rp-soft w-100" data-bs-dismiss="modal">Close</button>
                        @endif
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
