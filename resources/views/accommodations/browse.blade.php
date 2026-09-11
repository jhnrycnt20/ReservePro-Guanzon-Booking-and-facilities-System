@extends('layouts.public')

@section('title', 'Browse Resort')

@section('content')
<div class="container rp-public-page-top pb-4">

@php
    $checkInDisplay = request('check_in') ? \Carbon\Carbon::parse(request('check_in'))->format('M j, Y') : '';
    $checkOutDisplay = request('check_out') ? \Carbon\Carbon::parse(request('check_out'))->format('M j, Y') : '';
@endphp

<div class="rp-card mb-4">
    <form method="GET" action="{{ route('accommodations.browse') }}" class="row g-3 align-items-end" data-rp-availability-form>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All types</option>
                @foreach($types ?? [] as $type)
                    <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Check-in</label>
            <div class="rp-avail-input-wrap" data-rp-open-calendar>
                <input type="text" class="rp-avail-input" value="{{ $checkInDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                <i class="bi bi-calendar3 rp-avail-input-icon"></i>
            </div>
            <input type="hidden" name="check_in" value="{{ request('check_in') }}" data-stay-check-in>
        </div>
        <div class="col-md-3">
            <label class="form-label">Check-out</label>
            <div class="rp-avail-input-wrap" data-rp-open-calendar>
                <input type="text" class="rp-avail-input" value="{{ $checkOutDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                <i class="bi bi-calendar3 rp-avail-input-icon"></i>
            </div>
            <input type="hidden" name="check_out" value="{{ request('check_out') }}" data-stay-check-out>
        </div>
        <div class="col-md-3">
            <button type="submit" class="rp-btn-check-availability">Check Availability</button>
        </div>
    </form>
</div>

@include('partials.availability-calendar')

<div class="row g-4">
    @forelse($accommodations ?? [] as $item)
        <div class="col-md-4">
            <a href="{{ route('accommodations.show', array_filter(['accommodation' => $item->id, 'check_in' => request('check_in'), 'check_out' => request('check_out')])) }}" class="rp-cottage-card">
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                <div class="rp-cottage-card-body">
                    <div class="rp-cottage-title">{{ $item->name }}</div>
                    <div class="rp-cottage-subtitle">{{ $item->type->name ?? $item->accommodationType->name ?? 'Accommodation' }}</div>
                    <div class="rp-cottage-row">
                        <span>Rate</span>
                        <span>₱{{ number_format($item->rate, 0) }}</span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>Max guests</span>
                        <span>{{ $item->capacity }}</span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>Status</span>
                        <span>{{ ucfirst($item->status->value ?? $item->status) }}</span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="rp-card text-muted">No accommodations match your search.</div></div>
    @endforelse
</div>

@if(isset($accommodations) && method_exists($accommodations, 'links'))
    <div class="mt-4">{{ $accommodations->withQueryString()->links() }}</div>
@endif

</div>
@endsection
