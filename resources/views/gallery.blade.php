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
        <p class="rp-gallery-intro-subtext">Morning light on cabana porches, sea-view balconies, and long tables set for shared meals: small details that shape the rhythm of a stay here.</p>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="rp-gallery-grid" id="rpGalleryGrid">
            @foreach ([
                ['src' => asset('images/rooms/cabana/01-exterior-row.png'), 'title' => 'Cabana Row'],
                ['src' => asset('images/rooms/cabana/02-exterior-unit.png'), 'title' => 'Cabana Exterior'],
                ['src' => asset('images/rooms/cabana/03-exterior-porch.png'), 'title' => 'Cabana Porch'],
                ['src' => asset('images/rooms/cabana/04-exterior-evening.png'), 'title' => 'Cabana Evening'],
                ['src' => asset('images/rooms/cabana/05-bedroom.png'), 'title' => 'Cabana Bedroom'],
                ['src' => asset('images/rooms/cabana/06-bedroom-ac.png'), 'title' => 'Cabana Suite'],
                ['src' => asset('images/rooms/cabana/07-bedroom-towels.png'), 'title' => 'Cabana Interior'],
                ['src' => asset('images/rooms/cabana/08-bathroom.png'), 'title' => 'Cabana Bathroom'],
                ['src' => asset('images/rooms/cabana/09-bathroom-vanity.png'), 'title' => 'Cabana Vanity'],
                ['src' => asset('images/rooms/cabana/10-outdoor-grill.png'), 'title' => 'Outdoor Grill Area'],
                ['src' => asset('images/rooms/suite/01-exterior-row.png'), 'title' => 'Suite Room Exterior'],
                ['src' => asset('images/rooms/suite/03-balcony-sea-view.png'), 'title' => 'Suite Sea View'],
                ['src' => asset('images/rooms/suite/07-bedroom-sea-view.png'), 'title' => 'Suite Bedroom'],
                ['src' => asset('images/rooms/suite/05-outdoor-dining.png'), 'title' => 'Suite Outdoor Dining'],
                ['src' => asset('images/rooms/cottage/01-exterior-front.png'), 'title' => 'Open Cottage Exterior'],
                ['src' => asset('images/rooms/cottage/03-interior-long-table.png'), 'title' => 'Open Cottage Dining'],
                ['src' => asset('images/rooms/cottage/05-bamboo-lounge.png'), 'title' => 'Open Cottage Lounge'],
                ['src' => asset('images/rooms/cottage/06-picnic-tables.png'), 'title' => 'Picnic Tables'],
                ['src' => 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1200&q=80', 'title' => 'The Pool'],
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
    <div class="rp-gallery-banner-bg" style="background-image: url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1800&q=80');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
</div>
@endsection
