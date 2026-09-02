@extends('layouts.public')

@section('title', 'Offers')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>OFFERS</span></h1>
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

<section class="py-5 rp-offers-section">
    <div class="container">
        <div class="rp-offers-layout">
            <div class="rp-offers-content" id="rpOffersContent">
                @foreach ([
                    [
                        'title' => 'Sun & Splash Day Pass',
                        'description' => 'Full pool and beach access from 8:00 AM to 5:00 PM, with a shaded table reserved for your group. Available every day of the week.',
                        'image' => 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'title' => 'Cottage Getaway',
                        'description' => 'Overnight stay in our private cottages surrounded by nature. Perfect for families, couples, or barkadas looking for a relaxing escape.',
                        'image' => 'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'title' => 'Family Reunion Villa',
                        'description' => 'The Sunset Villa sleeps up to 10 guests, ideal for reunions and milestone celebrations by the shore.',
                        'image' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'title' => 'Midweek Room Rate',
                        'description' => 'Book a garden room Monday through Thursday for a quieter stay at our standard overnight rate.',
                        'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80',
                    ],
                ] as $offer)
                    <div class="rp-offer-row">
                        <div class="rp-offer-row-media">
                            <img src="{{ $offer['image'] }}" alt="{{ $offer['title'] }}" loading="lazy">
                        </div>
                        <div class="rp-offer-row-content">
                            <h2 class="rp-offer-row-title">{{ $offer['title'] }}</h2>
                            <span class="rp-offer-row-title-line"></span>
                            <p class="rp-offer-row-desc">{{ $offer['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <button type="button" class="rp-offers-next-btn" id="rpOffersNextBtn" aria-label="Show next offers">
        <i class="bi bi-arrow-right"></i>
    </button>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1800&q=80');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
</div>
@endsection
