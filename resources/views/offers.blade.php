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
                @forelse(($promoOffers ?? collect()) as $offer)
                    <div class="rp-offer-row">
                        <div class="rp-offer-row-media">
                            <img src="{{ $offer['image'] }}" alt="{{ $offer['title'] }}" loading="lazy">
                        </div>
                        <div class="rp-offer-row-content">
                            <h2 class="rp-offer-row-title">{{ $offer['title'] }}</h2>
                            <div class="rp-offer-price-row">
                                <strong>{{ $offer['promo_rate'] }}</strong>
                                <del>{{ $offer['regular_rate'] }}</del>
                                <span>{{ $offer['discount_label'] }}</span>
                            </div>
                            <p class="rp-offer-capacity">Good for {{ $offer['capacity'] }} {{ (int) $offer['capacity'] === 1 ? 'guest' : 'guests' }}</p>
                            @if(!empty($offer['perks']))
                                <ul class="rp-offer-perks">
                                    @foreach($offer['perks'] as $perk)
                                        <li>{{ $perk }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!empty($offer['cta']))
                                <a href="{{ $offer['cta'] }}" class="rp-offer-cta">
                                    <span>View room</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rp-offer-empty">
                        <h2 class="rp-offer-row-title">No active promo rooms right now</h2>
                        <p class="rp-offer-row-desc mb-0">Check back soon, or browse all accommodations to book a stay.</p>
                        <a href="{{ route('accommodations.browse', ['from' => 'offers']) }}" class="rp-offer-cta">Browse rooms</a>
                    </div>
                @endforelse

                {{-- Always-on resort packages (marketing) — not tied to private promo codes --}}
                @foreach ([
                    [
                        'title' => 'AC Room (with / without Videoke)',
                        'description' => 'Air-conditioned overnight rooms for day or night use.',
                        'perks' => [
                            'Without videoke ₱1,500 (max 4 persons)',
                            'With videoke ₱2,500 (max 8 persons)',
                            'Complimentary for 2 persons',
                            'Air conditioning',
                            'Private bathroom',
                            'Television',
                            'Wi-Fi',
                        ],
                        'image' => asset('images/rooms/cabana/Warm Modern Hotel Bedroom.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'room')->value('id'),
                            'from' => 'offers',
                        ]),
                        'cta_label' => 'View AC Rooms',
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
                        'image' => asset('images/rooms/suite/Coastal Bedroom with Ocean View.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'suite')->value('id'),
                            'from' => 'offers',
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
                        'image' => asset('images/rooms/cottage/Resort Pavilion Dining Terrace.png'),
                        'cta' => route('accommodations.browse', [
                            'type' => \App\Models\AccommodationType::query()->where('slug', 'cottage')->value('id'),
                            'from' => 'offers',
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
                            <p class="rp-offer-row-desc">{{ $offer['description'] }}</p>
                            @if(!empty($offer['perks']))
                                <ul class="rp-offer-perks">
                                    @foreach(collect($offer['perks'])->take(3) as $perk)
                                        <li>{{ $perk }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if(!empty($offer['cta']))
                                <a href="{{ $offer['cta'] }}" class="rp-offer-cta">
                                    <span>{{ $offer['cta_label'] ?? 'View room' }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <button type="button" class="rp-offers-next-btn" id="rpOffersNextBtn" aria-label="Show next offers">
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </button>
</section>

<div class="rp-gallery-banner">
    <div class="rp-gallery-banner-bg" style="background-image: url('{{ asset('images/landing-hero.png') }}');"></div>
    <img class="rp-gallery-banner-logo" src="{{ asset('images/guanzon_navbar_transparent.png') }}?v={{ filemtime(public_path('images/guanzon_navbar_transparent.png')) }}" alt="Guanzon Resort">
</div>
@endsection
