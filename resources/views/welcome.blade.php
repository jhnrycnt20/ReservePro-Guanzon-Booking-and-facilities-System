@extends('layouts.public')

@section('title', 'Resort Stay Made Simple')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>GUANZON</span></h1>
            <div class="rp-hero-subtext">Resort</div>
        </div>
    </div>
    <div class="rp-hero-scroll">
        <span class="rp-hero-scroll-line"></span>
        <span class="rp-hero-scroll-chevrons">
            <i class="bi bi-chevron-down"></i>
            <i class="bi bi-chevron-down"></i>
        </span>
    </div>
</section>

<section class="py-5 rp-story-section">
    <div class="container">
        <h2 class="rp-story-heading">Unwind in the Heart of Guanzon</h2>
        <div class="rp-story-text">
            <p>Tucked along a quiet stretch of shoreline, Guanzon Resort was built for guests who want the pace of a getaway without giving up comfort. Rooms and cottages sit close enough to the water to catch the breeze, yet far enough from the road to stay peaceful, giving every stay a natural rhythm of rest.</p>
        </div>
        <div class="rp-story-image">
            <img src="{{ asset('images/story-entrance.png') }}" alt="Guanzon Beach entrance">
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <h2 class="rp-plan-heading">Choose the best date for your stay</h2>
            </div>
            <div class="col-lg-7">
                <div class="rp-plan-slider" id="rpPlanSlider">
                    <div class="rp-plan-track">
                        <div class="rp-plan-card">
                            <img src="{{ asset('images/rooms/cottage/01-exterior-front.png') }}" alt="Guanzon Resort cottage" draggable="false">
                            <div class="rp-plan-card-title">Cottage</div>
                        </div>
                        <div class="rp-plan-card rp-plan-card-offset">
                            <img src="{{ asset('images/landing-hero.png') }}" alt="Guanzon Resort beach" draggable="false">
                            <div class="rp-plan-card-title">Beach</div>
                        </div>
                        <div class="rp-plan-card">
                            <img src="{{ asset('images/rooms/suite/07-bedroom-sea-view.png') }}" alt="Guanzon Resort room" draggable="false">
                            <div class="rp-plan-card-title">Room</div>
                        </div>
                        <div class="rp-plan-card rp-plan-card-offset">
                            <img src="{{ asset('images/rooms/suite/05-outdoor-dining.png') }}" alt="Guanzon Resort dining" draggable="false">
                            <div class="rp-plan-card-title">Dining</div>
                        </div>
                        <div class="rp-plan-card">
                            <img src="{{ asset('images/rooms/cabana/01-exterior-row.png') }}" alt="Guanzon Resort cabana" draggable="false">
                            <div class="rp-plan-card-title">Cabana</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 rp-cottages-section">
    <div class="container">
        <div class="rp-cottages-kicker">VALUE FILLED STAY</div>
        <h2 class="rp-cottages-heading">Our Rooms</h2>
        <div class="row g-4 mt-2">
            @forelse($featuredAccommodations ?? [] as $item)
                @php
                    $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
                    $isMaintenance = $statusValue === 'maintenance';
                @endphp
                <div class="col-md-4">
                    @if($isMaintenance)
                        <a
                            href="{{ route('accommodations.browse', ['type' => $item->accommodation_type_id, 'from' => 'home']) }}"
                            class="rp-cottage-card rp-cottage-card--blocked"
                            data-rp-blocked-click="Sorry, this room is under maintenance."
                            data-rp-blocked-title="Under maintenance"
                            aria-disabled="true"
                        >
                    @else
                        <a href="{{ route('accommodations.browse', ['type' => $item->accommodation_type_id, 'from' => 'home']) }}" class="rp-cottage-card">
                    @endif
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                        <div class="rp-cottage-card-body">
                            <div class="rp-cottage-title">{{ $item->name }}</div>
                            <div class="rp-cottage-subtitle">{{ $item->type->name ?? 'Accommodation' }}</div>
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
                <div class="col-12 text-muted">Accommodation listings will appear here once configured.</div>
            @endforelse
        </div>
        <div class="rp-view-rates-wrap">
            <a href="{{ route('accommodations.browse', ['from' => 'home']) }}" class="rp-view-rates-btn">
                VIEW MORE <span class="rp-view-rates-arrow">→</span>
            </a>
        </div>
    </div>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('{{ asset('images/landing-hero.png') }}');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logo_green.png') }}" alt="Guanzon Resort">
</div>

@if(($publicFeedback ?? collect())->isNotEmpty())
<section class="py-5 rp-feedback-section">
    <div class="container">
        <div class="rp-cottages-kicker">GUEST STORIES</div>
        <h2 class="rp-cottages-heading">What guests say</h2>
        <div class="row g-4 mt-2">
            @foreach($publicFeedback as $item)
                <div class="col-md-4">
                    <div class="rp-feedback-card">
                        <div class="rp-feedback-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= (int) $item->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                        <p class="rp-feedback-comment">“{{ \Illuminate\Support\Str::limit($item->comment, 160) }}”</p>
                        <div class="rp-feedback-author">{{ $item->guest?->user?->name ?? 'Guest' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
