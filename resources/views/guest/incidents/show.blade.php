@extends('layouts.public')

@section('title', 'Report '.$report->report_number)

@section('content')
@php
    $reportType = $report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type;
    $reportTypeLabel = $reportType ? str_replace('_', ' ', ucfirst($reportType)) : 'Report';
    $issueTitle = $report->title ?: \Illuminate\Support\Str::limit((string) $report->description, 80);
@endphp

<div class="container rp-public-page-top pb-5">
    <div class="rp-guest-report-page">
        <a href="{{ route('guest.incidents.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reports</a>
        <h1 class="rp-guest-report-title">{{ $issueTitle }}</h1>

        <section class="rp-guest-report-card">
            @include('partials.guest-report-progress', ['report' => $report])
        </section>

        <section class="rp-guest-report-card">
            <div class="rp-guest-report-card-head">
                <span class="rp-guest-report-card-icon" aria-hidden="true"><i class="bi bi-clipboard2-data"></i></span>
                <h2 class="rp-guest-report-card-title">Report Details</h2>
            </div>

            <div class="rp-guest-report-meta">
                <div class="rp-guest-report-meta-item">
                    <div class="rp-guest-report-meta-label">Record #</div>
                    <div class="rp-guest-report-meta-value">
                        <i class="bi bi-hash" aria-hidden="true"></i>
                        <span>{{ $report->report_number }}</span>
                    </div>
                </div>
                <div class="rp-guest-report-meta-item">
                    <div class="rp-guest-report-meta-label">Location</div>
                    <div class="rp-guest-report-meta-value">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <span>{{ $report->location ?: '—' }}</span>
                    </div>
                </div>
                <div class="rp-guest-report-meta-item">
                    <div class="rp-guest-report-meta-label">Type</div>
                    <div class="rp-guest-report-meta-value">
                        <i class="bi bi-tag" aria-hidden="true"></i>
                        <span>{{ $reportTypeLabel }}</span>
                    </div>
                </div>
                <div class="rp-guest-report-meta-item">
                    <div class="rp-guest-report-meta-label">Status</div>
                    <div class="rp-guest-report-meta-value">
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <x-status-badge :status="$report->status" />
                    </div>
                </div>
            </div>
        </section>

        <section class="rp-guest-report-card">
            <div class="rp-guest-report-card-head">
                <span class="rp-guest-report-card-icon" aria-hidden="true"><i class="bi bi-chat-left-text"></i></span>
                <h2 class="rp-guest-report-card-title">Description</h2>
            </div>
            <p class="rp-guest-report-copy mb-0">{{ $report->description }}</p>
        </section>

        @if($report->photo)
            <section class="rp-guest-report-card">
                <div class="rp-guest-report-card-head">
                    <span class="rp-guest-report-card-icon" aria-hidden="true"><i class="bi bi-image"></i></span>
                    <h2 class="rp-guest-report-card-title">Photo</h2>
                </div>
                <div class="rp-guest-report-photo-frame">
                    <button type="button" class="rp-guest-report-photo-trigger" data-bs-toggle="modal" data-bs-target="#rpGuestReportPhotoModal" aria-label="View report photo">
                        <img src="{{ asset('storage/'.$report->photo) }}" alt="Report photo">
                    </button>
                </div>
            </section>
        @endif

        @if($report->investigation_notes || $report->resolution_notes || $report->invalid_reason)
            <section class="rp-guest-report-card">
                <div class="rp-guest-report-card-head">
                    <span class="rp-guest-report-card-icon" aria-hidden="true"><i class="bi bi-journal-text"></i></span>
                    <h2 class="rp-guest-report-card-title">Updates</h2>
                </div>

                @if($report->investigation_notes)
                    <div class="rp-guest-report-update">
                        <div class="rp-guest-report-meta-label">Investigation notes</div>
                        <p class="rp-guest-report-copy mb-0">{{ $report->investigation_notes }}</p>
                    </div>
                @endif
                @if($report->resolution_notes)
                    <div class="rp-guest-report-update {{ $report->investigation_notes ? 'mt-3' : '' }}">
                        <div class="rp-guest-report-meta-label">Resolution notes</div>
                        <p class="rp-guest-report-copy mb-0">{{ $report->resolution_notes }}</p>
                    </div>
                @endif
                @if($report->invalid_reason)
                    <div class="alert alert-danger mb-0 {{ ($report->investigation_notes || $report->resolution_notes) ? 'mt-3' : '' }}">
                        Invalid reason: {{ $report->invalid_reason }}
                    </div>
                @endif
            </section>
        @endif
    </div>
</div>

@if($report->photo)
<div class="modal fade" id="rpGuestReportPhotoModal" tabindex="-1" aria-labelledby="rpGuestReportPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-guest-report-photo-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpGuestReportPhotoModalLabel">Photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <img src="{{ asset('storage/'.$report->photo) }}" class="rp-guest-report-photo-modal-img" alt="Report photo full size">
            </div>
        </div>
    </div>
</div>
@endif
@endsection
