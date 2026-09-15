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
                        'title' => 'Cabana Promo Rate',
                        'description' => 'Avail our Promo Rates ₱2,500 (good for 4 pax).',
                        'perks' => [
                            'Aircon',
                            'Smart TV',
                            'Hot and Cold shower',
                            'Toiletries',
                            'Complimentary coffee & water',
                            'Electric kettle',
                            'Pay Wifi',
                            'Balcony',
                            'No pets',
                            'No Visitors',
                        ],
                        'image' => asset('images/offers/cabana-promo.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'cabana')->value('id'),
                        ]),
                        'cta_label' => 'View Cabana',
                    ],
                    [
                        'title' => 'Suite Room at Guanzon Beach',
                        'description' => 'Rate: ₱2,500. Bed good for 5 pax with sea view.',
                        'perks' => [
                            'Table and Chair',
                            'Videoke Machine (optional)',
                            'Mini Refrigerator',
                            'Griller',
                            'Toilet',
                            'Bed good for 5 pax',
                            'Aircondition Room',
                            'Cable TV',
                            'Hot Shower',
                            'Shampoo and Soap',
                            'Water and Coffee',
                            'No Corkage on foods and drinks',
                            'Sea View',
                        ],
                        'image' => asset('images/offers/suite-promo.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'suite')->value('id'),
                        ]),
                        'cta_label' => 'View Suite Room',
                    ],
                    [
                        'title' => 'Guanzon Beach Open Cottage',
                        'description' => 'Open-air cottage for day or overnight barkada stays.',
                        'perks' => [
                            'Day use ₱1,700 (8:00 AM–5:00 PM)',
                            'Night use ₱1,700 (6:00 PM–6:00 AM)',
                            'Videoke ₱1,000 (optional)',
                            'Covered pavilion seating',
                            'Tables and chairs',
                            'Outdoor resort setting',
                        ],
                        'image' => asset('images/offers/open-cottage-promo.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'cottage')->value('id'),
                        ]),
                        'cta_label' => 'View Open Cottage',
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
                            @if(!empty($offer['perks']))
                                <ul class="rp-offer-perks">
                                    @foreach($offer['perks'] as $perk)
                                        <li>{{ $perk }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!empty($offer['cta']))
                                <a href="{{ $offer['cta'] }}" class="rp-offer-cta">{{ $offer['cta_label'] ?? 'Learn more' }}</a>
                            @endif
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
    <div class="rp-gallery-banner-bg" style="background-image: url('{{ asset('images/offers/cabana-promo.png') }}');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
</div>
@endsection
