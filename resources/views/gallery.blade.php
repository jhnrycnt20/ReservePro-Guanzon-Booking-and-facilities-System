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
                ['src' => asset('images/rooms/suite/Vivid Resort Suites Under Blue Skies.png'), 'title' => 'Resort Suites'],
                ['src' => asset('images/rooms/suite/Resort Terrace Framing the Ocean.png'), 'title' => 'Ocean Terrace'],
                ['src' => asset('images/rooms/suite/Coastal Mustard Porch Entrance.png'), 'title' => 'Suite Porch Entrance'],
                ['src' => asset('images/rooms/suite/Coastal Bedroom with Ocean View.png'), 'title' => 'Ocean-view Bedroom'],
                ['src' => asset('images/rooms/suite/Bright Modern Hotel Room with Navy Curtains.png'), 'title' => 'Modern Suite Room'],
                ['src' => asset('images/rooms/suite/Bright Modern Beige-Tiled Bathroom.png'), 'title' => 'Suite Bathroom'],
                ['src' => asset('images/rooms/cabana/Sunset Modern Bungalow Unit 4.png'), 'title' => 'Bungalow Unit 4'],
                ['src' => asset('images/rooms/cabana/Modern Bungalow Row at Sunset.png'), 'title' => 'Bungalow Row'],
                ['src' => asset('images/rooms/cabana/Warm Modern Hotel Bedroom.png'), 'title' => 'Cabana Bedroom'],
                ['src' => asset('images/rooms/cabana/Modern Bedroom with Towel Stacks and Smart TV.png'), 'title' => 'Cabana Interior'],
                ['src' => asset('images/rooms/cabana/Warm Modern Bathroom with Pink Shower Curtain.png'), 'title' => 'Cabana Bathroom'],
                ['src' => asset('images/rooms/cabana/Modern Bamboo-Screen Outdoor Sink and Grill.png'), 'title' => 'Outdoor Sink and Grill'],
                ['src' => asset('images/rooms/cottage/Tree-Lined Pavilion Walkway.png'), 'title' => 'Pavilion Walkway'],
                ['src' => asset('images/rooms/cottage/Sunlit Picnic Pavilion Courtyard.png'), 'title' => 'Pavilion Courtyard'],
                ['src' => asset('images/rooms/cottage/Resort Pavilion Dining Terrace.png'), 'title' => 'Pavilion Dining Terrace'],
                ['src' => asset('images/rooms/ocean-view-room.png'), 'title' => 'Ocean-view Room'],
                ['src' => asset('images/rooms/garden-cabin.png'), 'title' => 'Garden Cabin'],
                ['src' => asset('images/rooms/blue-cabana.png'), 'title' => 'Blue Cabana'],
                ['src' => asset('images/rooms/bamboo-lounge.png'), 'title' => 'Bamboo Lounge'],
            ] as $photo)
                <div class="rp-gallery-item">
                    <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('{{ asset('images/landing-hero.png') }}');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_navbar_transparent.png') }}?v={{ filemtime(public_path('images/guanzon_navbar_transparent.png')) }}" alt="Guanzon Resort">
</div>
@endsection
