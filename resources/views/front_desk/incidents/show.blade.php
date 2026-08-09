@extends('layouts.dashboard')

@section('title', 'Resolve Report')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Resolve '.$report->report_number)
@section('page_subtitle', 'Plan action for verified reports and update progress')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="h5 mb-0">{{ $report->title }}</h2>
                <x-status-badge :status="$report->status" />
            </div>
            <p>{{ $report->description }}</p>
            <div class="text-muted small">Location</div>
            <div class="mb-3">{{ $report->location }}</div>
            <div class="text-muted small">Security investigation notes</div>
            <div class="mb-3">{{ $report->investigation_notes ?? '—' }}</div>
            @if($report->investigation_photo)
                <img src="{{ asset('storage/'.$report->investigation_photo) }}" class="img-fluid rounded" alt="Investigation photo">
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        @php $status = $report->status instanceof \BackedEnum ? $report->status->value : $report->status; @endphp
        @if($status === 'verified')
            <div class="rp-card">
                <form method="POST" action="{{ route('front_desk.incidents.progress', $report) }}">
                    @csrf
                    <label class="form-label">Resolution action</label>
                    <select name="resolution_action" class="form-select mb-2" required>
                        <option value="repair_broken_amenity">Repair Broken Amenity</option>
                        <option value="assist_guest">Assist Guest</option>
                        <option value="contact_maintenance">Contact Maintenance</option>
                        <option value="contact_security">Contact Security</option>
                        <option value="other">Other</option>
                    </select>
                    <textarea name="resolution_notes" class="form-control mb-2" rows="4" placeholder="Action plan / notes" required></textarea>
                    <button class="btn btn-rp-primary w-100">Mark In Progress</button>
                </form>
            </div>
        @elseif($status === 'in_progress')
            <div class="rp-card">
                <form method="POST" action="{{ route('front_desk.incidents.resolve', $report) }}">
                    @csrf
                    <textarea name="resolution_notes" class="form-control mb-2" rows="4" placeholder="Verify resolution notes" required>{{ old('resolution_notes', $report->resolution_notes) }}</textarea>
                    <button class="btn btn-success w-100">Mark as Resolved</button>
                </form>
            </div>
        @else
            <div class="rp-card">
                <div class="text-muted small">Resolution notes</div>
                <p class="mb-0">{{ $report->resolution_notes ?? '—' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
