@extends('layouts.public')

@section('title', 'Gallery')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>GALLERY</span></h1>
            <div class="rp-hero-subtext">Guanzon Resort</div>
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

<section class="pt-5 pb-4">
    <div class="container">
        <h2 class="rp-gallery-intro-heading">A glimpse of what awaits you</h2>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="rp-gallery-grid" id="rpGalleryGrid">
            @foreach ([
                ['src' => asset('images/rooms/ocean-view-room.png'), 'title' => 'Ocean View Room'],
                ['src' => 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1200&q=80', 'title' => 'The Pool'],
                ['src' => asset('images/rooms/bamboo-lounge.png'), 'title' => 'Bamboo Lounge'],
                ['src' => 'https://images.unsplash.com/photo-1611048267451-e6ed903d4a38?auto=format&fit=crop&w=1200&q=80', 'title' => 'Deluxe Room'],
                ['src' => asset('images/rooms/blue-cabana.png'), 'title' => 'Blue Cabana'],
                ['src' => 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=1200&q=80', 'title' => 'Poolside Dining'],
                ['src' => 'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?auto=format&fit=crop&w=1200&q=80', 'title' => 'Garden Cottage'],
                ['src' => asset('images/rooms/garden-cabin.png'), 'title' => 'Pine Cabin'],
                ['src' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80', 'title' => 'Guest Room View'],
                ['src' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1200&q=80', 'title' => 'Beachfront Villa'],
                ['src' => 'https://images.unsplash.com/photo-1615880484746-a134be9a6ecf?auto=format&fit=crop&w=1200&q=80', 'title' => 'Poolside Cabana'],
                ['src' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80', 'title' => 'Family Suite'],
            ] as $photo)
                <div class="rp-gallery-item">
                    <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1800&q=80');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
</div>
@endsection
