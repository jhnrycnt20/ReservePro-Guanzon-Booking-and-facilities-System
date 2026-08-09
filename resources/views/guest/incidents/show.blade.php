@extends('layouts.dashboard')

@section('title', 'Report '.$report->report_number)
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Report '.$report->report_number)
@section('page_subtitle', 'Track updates from security and front desk')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="rp-card">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h2 class="h5 mb-1">{{ $report->title }}</h2>
            <div class="text-muted">{{ $report->location }}</div>
        </div>
        <x-status-badge :status="$report->status" />
    </div>
    <p>{{ $report->description }}</p>
    @if($report->photo)
        <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded mb-3" alt="Report photo">
    @endif
    @if($report->investigation_notes)
        <hr>
        <div class="text-muted small">Investigation notes</div>
        <p>{{ $report->investigation_notes }}</p>
    @endif
    @if($report->resolution_notes)
        <div class="text-muted small">Resolution notes</div>
        <p>{{ $report->resolution_notes }}</p>
    @endif
    @if($report->invalid_reason)
        <div class="alert alert-danger">Invalid reason: {{ $report->invalid_reason }}</div>
    @endif
</div>
@endsection
