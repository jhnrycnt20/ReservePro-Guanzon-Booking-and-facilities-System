@extends('layouts.dashboard')

@section('title', 'Investigate Report')
@section('theme', 'security')
@section('role_label', 'Security Guard')
@section('page_title', 'Investigate '.$report->report_number)
@section('page_subtitle', 'Validate the report on site, then verify or mark invalid')
@section('sidebar')
    @include('partials.sidebar-security')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card mb-3">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ $report->title }}</h2>
                    <div class="text-muted">{{ $report->location }} · {{ str_replace('_', ' ', ucfirst($report->report_type instanceof \BackedEnum ? $report->report_type->value : $report->report_type)) }}</div>
                </div>
                <x-status-badge :status="$report->status" />
            </div>
            <p>{{ $report->description }}</p>
            @if($report->photo)
                <img src="{{ asset('storage/'.$report->photo) }}" class="img-fluid rounded" alt="Report photo">
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        @if(($report->status instanceof \BackedEnum ? $report->status->value : $report->status) === 'pending')
            <div class="rp-card mb-3">
                <h3 class="h6">Mark as Verified</h3>
                <form method="POST" action="{{ route('security.incidents.verify', $report) }}" enctype="multipart/form-data">
                    @csrf
                    <textarea name="investigation_notes" class="form-control mb-2" rows="4" placeholder="Investigation notes / findings" required>{{ old('investigation_notes') }}</textarea>
                    <input type="file" name="investigation_photo" class="form-control mb-2" accept="image/*">
                    <button class="btn btn-success w-100">Verify & Forward to Front Desk</button>
                </form>
            </div>
            <div class="rp-card">
                <h3 class="h6">Mark as Invalid</h3>
                <form method="POST" action="{{ route('security.incidents.invalidate', $report) }}">
                    @csrf
                    <textarea name="invalid_reason" class="form-control mb-2" rows="3" placeholder="Reason / notes" required>{{ old('invalid_reason') }}</textarea>
                    <button class="btn btn-outline-danger w-100">Mark Invalid & Close</button>
                </form>
            </div>
        @else
            <div class="rp-card">
                <h3 class="h6">Investigation Notes</h3>
                <p class="mb-0">{{ $report->investigation_notes ?? $report->invalid_reason ?? '—' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
