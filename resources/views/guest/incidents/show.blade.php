@extends('layouts.public')

@section('title', 'Report '.$report->report_number)

@section('content')
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.incidents.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reports</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">{{ $report->title }}</h1>
    </div>

    <div class="rp-flow-card mb-4">
        <div class="rp-cottage-row">
            <span>Report #</span>
            <span>{{ $report->report_number }}</span>
        </div>
        <div class="rp-cottage-row">
            <span>Type</span>
            <span>{{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</span>
        </div>
        <div class="rp-cottage-row">
            <span>Location</span>
            <span>{{ $report->location }}</span>
        </div>
        <div class="rp-cottage-row">
            <span>Status</span>
            <x-status-badge :status="$report->status" />
        </div>
    </div>

    <div class="rp-flow-card mb-4">
        <h3 class="h6">Description</h3>
        <p class="mb-0">{{ $report->description }}</p>
    </div>

    @if($report->photo)
        <div class="rp-flow-card mb-4">
            <h3 class="h6">Photo</h3>
            <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded" alt="Report photo">
        </div>
    @endif

    @if($report->investigation_notes || $report->resolution_notes || $report->invalid_reason)
        <div class="rp-flow-card">
            @if($report->investigation_notes)
                <h3 class="h6">Investigation notes</h3>
                <p>{{ $report->investigation_notes }}</p>
            @endif
            @if($report->resolution_notes)
                <h3 class="h6 {{ $report->investigation_notes ? 'mt-3' : '' }}">Resolution notes</h3>
                <p class="mb-0">{{ $report->resolution_notes }}</p>
            @endif
            @if($report->invalid_reason)
                <div class="alert alert-danger mb-0">Invalid reason: {{ $report->invalid_reason }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
