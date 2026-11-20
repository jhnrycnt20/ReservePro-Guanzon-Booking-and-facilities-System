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
    $actionLabels = [
        'repair_broken_amenity' => 'Repair broken amenity',
        'assist_guest' => 'Assist guest',
        'contact_maintenance' => 'Contact maintenance',
        'contact_security' => 'Contact security',
        'other' => 'Other',
    ];
    $statusIcon = match ($status) {
        'verified' => 'bi-shield-check',
        'in_progress' => 'bi-arrow-repeat',
        'resolved', 'closed' => 'bi-check-circle',
        'invalid' => 'bi-x-circle',
        default => 'bi-clock',
    };
@endphp

<div class="modal fade" id="rpFdReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpFdReportModal{{ $report->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable rp-fd-report-dialog">
        <div class="modal-content rp-admin-modal rp-admin-report-modal rp-fd-report-modal">
            <div class="modal-header">
                <div class="rp-admin-report-header-copy">
                    <h2 class="modal-title" id="rpFdReportModal{{ $report->id }}Label">{{ $issueTitle }}</h2>
                    <div class="rp-admin-report-header-meta">
                        <span class="rp-admin-report-number">{{ $report->report_number }}</span>
                        <span class="rp-report-detail-status is-{{ $status }}">
                            <i class="bi {{ $statusIcon }}" aria-hidden="true"></i> {{ $statusLabel }}
                        </span>
                    </div>
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
                                <div><div class="rp-report-detail-label">Report ID</div><div class="rp-report-detail-meta-value">{{ $report->report_number }}</div></div>
                                <div><div class="rp-report-detail-label">Report type</div><div class="rp-report-detail-meta-value">{{ $reportTypeLabel }}</div></div>
                                <div><div class="rp-report-detail-label">Location</div><div class="rp-report-detail-meta-value">{{ $report->location ?: '—' }}</div></div>
                                <div>
                                    <div class="rp-report-detail-label">Guest</div>
                                    <div class="rp-report-detail-meta-value">{{ $guestName }}</div>
                                </div>
                                <div>
                                    <div class="rp-report-detail-label">Reported</div>
                                    <div class="rp-report-detail-meta-value">{{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</div>
                                </div>
                                @if($bookingLabel)
                                    <div>
                                        <div class="rp-report-detail-label">Booking</div>
                                        <div class="rp-report-detail-meta-value">{{ $bookingLabel }}</div>
                                    </div>
                                @endif
                                @if($report->securityGuard)
                                    <div>
                                        <div class="rp-report-detail-label">Security</div>
                                        <div class="rp-report-detail-meta-value">{{ $report->securityGuard->name }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($report->photo)
                            <div class="rp-report-detail-block">
                                <h4 class="rp-report-detail-label">Guest photo</h4>
                                <div class="rp-report-detail-photo-wrap">
                                    <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpFdGuestPhotoModal{{ $report->id }}" aria-label="View guest photo">
                                        <img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-detail-photo" alt="Guest report photo">
                                    </button>
                                    <button type="button" class="rp-report-detail-photo-expand" data-bs-toggle="modal" data-bs-target="#rpFdGuestPhotoModal{{ $report->id }}" title="View full photo" aria-label="View full guest photo">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                    </section>

                    <aside class="rp-report-detail-card rp-report-review-card">
                        @if($report->investigation_notes || $report->investigation_photo)
                            <div class="rp-report-detail-block">
                                <h4 class="rp-report-detail-label">Security investigation</h4>
                                @if($report->investigation_notes)<p class="rp-report-detail-copy mb-0">{{ $report->investigation_notes }}</p>@endif
                                @if($report->investigation_photo)
                                    <div class="rp-report-detail-photo-wrap mt-3">
                                        <button type="button" class="rp-report-detail-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpFdInvestigationPhotoModal{{ $report->id }}" aria-label="View investigation photo"><img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-detail-photo" alt="Investigation photo"></button>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if($status === 'verified')
                            <div class="rp-report-review-head">
                                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-play-circle"></i></span>
                                <div>
                                    <h3 class="rp-report-review-title">Start resolution</h3>
                                    <p class="rp-report-review-sub mb-0">Choose an action and record what is being done.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('front_desk.incidents.progress', $report) }}" class="rp-report-review-form">
                                @csrf
                                <input type="hidden" name="_modal" value="{{ $report->id }}">
                                <div class="mb-3">
                                    <label class="form-label" for="rpResolutionAction{{ $report->id }}">Resolution action</label>
                                    <select id="rpResolutionAction{{ $report->id }}" name="resolution_action" class="form-select" required>
                                        @foreach($actionLabels as $value => $label)
                                            <option value="{{ $value }}" @selected(old('resolution_action', 'repair_broken_amenity') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('resolution_action')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="rpResolutionNotes{{ $report->id }}">Notes</label>
                                    <div class="rp-report-notes-wrap">
                                        <textarea id="rpResolutionNotes{{ $report->id }}" name="resolution_notes" class="form-control" rows="5" maxlength="1000" placeholder="What's being done to resolve this" required data-rp-notes-count>{{ old('resolution_notes') }}</textarea>
                                        <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                                    </div>
                                    @error('resolution_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit">
                                    <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i> Mark In Progress
                                </button>
                            </form>
                        @elseif($status === 'in_progress')
                            <div class="rp-report-review-head">
                                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                                <div>
                                    <h3 class="rp-report-review-title">Resolve report</h3>
                                    <p class="rp-report-review-sub mb-0">Confirm the completed work for the guest.</p>
                                </div>
                            </div>

                            @if($report->resolution_action)
                                <div class="rp-report-detail-block">
                                    <div class="rp-report-detail-label">Action in progress</div>
                                    <div class="rp-report-detail-meta-value">{{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}</div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('front_desk.incidents.resolve', $report) }}" class="rp-report-review-form">
                                @csrf
                                <input type="hidden" name="_modal" value="{{ $report->id }}">
                                <div class="mb-3">
                                    <label class="form-label" for="rpResolveNotes{{ $report->id }}">Resolution notes</label>
                                    <div class="rp-report-notes-wrap">
                                        <textarea id="rpResolveNotes{{ $report->id }}" name="resolution_notes" class="form-control" rows="5" maxlength="1000" placeholder="Confirm what was done to resolve this" required data-rp-notes-count>{{ old('resolution_notes', $report->resolution_notes) }}</textarea>
                                        <div class="rp-report-char-count"><span data-rp-notes-count-value>0</span>/1000</div>
                                    </div>
                                    @error('resolution_notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-rp-primary w-100 rp-report-submit">
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i> Mark as Resolved
                                </button>
                            </form>
                        @else
                            <div class="rp-report-review-head">
                                <span class="rp-report-review-icon" aria-hidden="true"><i class="bi {{ $statusIcon }}"></i></span>
                                <div>
                                    <h3 class="rp-report-review-title">{{ $status === 'invalid' ? 'Invalid report' : 'Resolution summary' }}</h3>
                                    <p class="rp-report-review-sub mb-0">Recorded outcome for this guest report.</p>
                                </div>
                            </div>

                            @if($status === 'invalid')
                                <div class="rp-report-detail-block">
                                    <div class="rp-report-detail-label">Reason</div>
                                    <div class="rp-report-note-panel"><p class="rp-report-detail-copy mb-0">{{ $report->invalid_reason ?? '—' }}</p></div>
                                </div>
                            @else
                                @if($report->resolution_action)
                                    <div class="rp-report-detail-block">
                                        <div class="rp-report-detail-label">Action taken</div>
                                        <div class="rp-report-detail-meta-value">{{ $actionLabels[$report->resolution_action] ?? str_replace('_', ' ', ucfirst($report->resolution_action)) }}</div>
                                    </div>
                                @endif
                                <div class="rp-report-detail-block">
                                    <div class="rp-report-detail-label">Resolution notes</div>
                                    <div class="rp-report-note-panel"><p class="rp-report-detail-copy mb-0">{{ $report->resolution_notes ?? '—' }}</p></div>
                                </div>
                            @endif

                            <button type="button" class="btn btn-rp-soft w-100" data-bs-dismiss="modal">Close</button>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

@if($report->photo)
<div class="modal fade" id="rpFdGuestPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpFdGuestPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-fd-photo-modal data-rp-return-modal="rpFdReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpFdGuestPhotoModal{{ $report->id }}Label">Guest photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2"><img src="{{ asset('storage/'.$report->photo) }}" class="rp-report-photo-modal-img" alt="Guest report photo full size"></div>
        </div>
    </div>
</div>
@endif

@if($report->investigation_photo)
<div class="modal fade" id="rpFdInvestigationPhotoModal{{ $report->id }}" tabindex="-1" aria-labelledby="rpFdInvestigationPhotoModal{{ $report->id }}Label" aria-hidden="true" data-rp-fd-photo-modal data-rp-return-modal="rpFdReportModal{{ $report->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpFdInvestigationPhotoModal{{ $report->id }}Label">Investigation photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Back to report"></button>
            </div>
            <div class="modal-body pt-2"><img src="{{ asset('storage/'.$report->investigation_photo) }}" class="rp-report-photo-modal-img" alt="Investigation photo full size"></div>
        </div>
    </div>
</div>
@endif
