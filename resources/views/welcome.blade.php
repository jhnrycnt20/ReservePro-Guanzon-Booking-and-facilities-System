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
            <p>From the moment a reservation is confirmed to the morning of check-out, our team keeps every detail in view — availability, payments, housekeeping, and support — so guests can spend their time on the things that matter: the shoreline, the quiet, and each other.</p>
        </div>
        <div class="rp-story-image">
            <img src="https://images.unsplash.com/photo-1552733407-5d5c46c3bb3b?auto=format&fit=crop&w=1800&q=80" alt="Guanzon Resort shoreline">
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
                            <img src="https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=700&q=80" alt="Guanzon Resort cottage" draggable="false">
                            <div class="rp-plan-card-title">Cottage</div>
                        </div>
                        <div class="rp-plan-card rp-plan-card-offset">
                            <img src="https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=700&q=80" alt="Guanzon Resort pool" draggable="false">
                            <div class="rp-plan-card-title">Pool</div>
                        </div>
                        <div class="rp-plan-card">
                            <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=700&q=80" alt="Guanzon Resort room" draggable="false">
                            <div class="rp-plan-card-title">Room</div>
                        </div>
                        <div class="rp-plan-card rp-plan-card-offset">
                            <img src="https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=700&q=80" alt="Guanzon Resort dining" draggable="false">
                            <div class="rp-plan-card-title">Dining</div>
                        </div>
                        <div class="rp-plan-card">
                            <img src="https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=700&q=80" alt="Guanzon Resort cabana" draggable="false">
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
        <p class="rp-cottages-lead text-muted mb-0">Explore the rooms and cottages Guanzon offers, then book your preferred stay.</p>
        <div class="row g-4 mt-2">
            @forelse($featuredAccommodations ?? [] as $item)
                <div class="col-md-4">
                    <a href="{{ route('accommodations.browse', ['type' => $item->accommodation_type_id]) }}" class="rp-cottage-card">
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
                                <span>{{ ucfirst($item->status->value ?? $item->status) }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 text-muted">Accommodation listings will appear here once configured.</div>
            @endforelse
        </div>
        <div class="rp-view-rates-wrap">
            <a href="{{ route('accommodations.browse') }}" class="rp-view-rates-btn">
                VIEW ALL MORE <span class="rp-view-rates-arrow">→</span>
            </a>
        </div>
    </div>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1800&q=80');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
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
