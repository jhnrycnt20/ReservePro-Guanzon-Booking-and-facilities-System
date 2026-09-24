@extends('layouts.public')

@section('title', 'Accommodations')

@section('content')
<div class="container rp-public-page-top pb-4">

@php
    $checkInDisplay = request('check_in') ? \Carbon\Carbon::parse(request('check_in'))->format('M j, Y') : '';
    $checkOutDisplay = request('check_out') ? \Carbon\Carbon::parse(request('check_out'))->format('M j, Y') : '';
@endphp

<div class="rp-page-intro">
    <h1 class="rp-page-intro-title">Accommodations</h1>
    <p class="rp-page-intro-text mb-0">Browse rooms, cabanas, suites, and cottages at Guanzon Beach.</p>
</div>

@if(session('error'))
    <div class="alert alert-warning border-0 shadow-sm mb-3" role="alert" data-rp-auto-dismiss>
        {{ session('error') }}
    </div>
@endif

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
        @php
            $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
            $isMaintenance = $statusValue === 'maintenance';
            $showUrl = route('accommodations.show', array_filter([
                'accommodation' => $item->id,
                'check_in' => request('check_in'),
                'check_out' => request('check_out'),
                'from' => request('from'),
            ]));
        @endphp
        <div class="col-md-4">
            @if($isMaintenance)
                <a
                    href="{{ $showUrl }}"
                    class="rp-cottage-card rp-cottage-card--blocked"
                    data-rp-blocked-click="Sorry, this room is under maintenance."
                    data-rp-blocked-title="Under maintenance"
                    aria-disabled="true"
                >
            @else
                <a href="{{ $showUrl }}" class="rp-cottage-card">
            @endif
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                <div class="rp-cottage-card-body">
                    <div class="rp-cottage-title">{{ $item->name }}</div>
                    @php $typeName = $item->type->name ?? $item->accommodationType->name ?? null; @endphp
                    @if($typeName && strcasecmp($typeName, $item->name) !== 0)
                        <div class="rp-cottage-subtitle">{{ $typeName }}</div>
                    @endif
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
                        <span>{{ ucfirst($statusValue) }}</span>
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
