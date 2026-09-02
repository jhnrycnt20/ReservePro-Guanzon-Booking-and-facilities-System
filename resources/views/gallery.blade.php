@extends('layouts.public')

@section('title', 'Gallery')

@section('content')
<section class="rp-page-hero">
    <div class="container">
        <div class="rp-kicker">Guanzon Resort</div>
        <h1>The Gallery</h1>
        <p>A closer look at the rooms, cottages, and shoreline that make every stay at Guanzon feel like a retreat.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="rp-gallery-filters" role="tablist">
            <button type="button" class="rp-gallery-filter is-active" data-filter="all">All</button>
            <button type="button" class="rp-gallery-filter" data-filter="rooms">Rooms</button>
            <button type="button" class="rp-gallery-filter" data-filter="cottages">Cottages &amp; Villas</button>
            <button type="button" class="rp-gallery-filter" data-filter="resort">Resort &amp; Grounds</button>
            <button type="button" class="rp-gallery-filter" data-filter="dining">Dining</button>
        </div>

        <div class="rp-gallery-grid" id="rpGalleryGrid">
            @foreach ([
                ['src' => asset('images/rooms/ocean-view-room.png'), 'category' => 'rooms', 'label' => 'Room', 'title' => 'Ocean View Room', 'size' => 'tall'],
                ['src' => 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=1200&q=80', 'category' => 'resort', 'label' => 'Resort', 'title' => 'The Pool', 'size' => 'wide'],
                ['src' => asset('images/rooms/bamboo-lounge.png'), 'category' => 'cottages', 'label' => 'Cottage', 'title' => 'Bamboo Lounge', 'size' => ''],
                ['src' => 'https://images.unsplash.com/photo-1611048267451-e6ed903d4a38?auto=format&fit=crop&w=1200&q=80', 'category' => 'rooms', 'label' => 'Room', 'title' => 'Deluxe Room', 'size' => ''],
                ['src' => asset('images/rooms/blue-cabana.png'), 'category' => 'cottages', 'label' => 'Cottage', 'title' => 'Blue Cabana', 'size' => 'tall'],
                ['src' => 'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=1200&q=80', 'category' => 'dining', 'label' => 'Dining', 'title' => 'Poolside Dining', 'size' => ''],
                ['src' => 'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?auto=format&fit=crop&w=1200&q=80', 'category' => 'cottages', 'label' => 'Cottage', 'title' => 'Garden Cottage', 'size' => ''],
                ['src' => asset('images/rooms/garden-cabin.png'), 'category' => 'rooms', 'label' => 'Cabin', 'title' => 'Pine Cabin', 'size' => 'wide'],
                ['src' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80', 'category' => 'resort', 'label' => 'Resort', 'title' => 'Guest Room View', 'size' => ''],
                ['src' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1200&q=80', 'category' => 'cottages', 'label' => 'Villa', 'title' => 'Beachfront Villa', 'size' => 'tall'],
                ['src' => 'https://images.unsplash.com/photo-1615880484746-a134be9a6ecf?auto=format&fit=crop&w=1200&q=80', 'category' => 'resort', 'label' => 'Resort', 'title' => 'Poolside Cabana', 'size' => ''],
                ['src' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1200&q=80', 'category' => 'rooms', 'label' => 'Suite', 'title' => 'Family Suite', 'size' => ''],
            ] as $photo)
                <div class="rp-gallery-item {{ $photo['size'] ? 'rp-gallery-item--'.$photo['size'] : '' }}" data-category="{{ $photo['category'] }}">
                    <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy">
                    <div class="rp-gallery-item-caption">
                        <span>{{ $photo['label'] }}</span>
                        <strong>{{ $photo['title'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
