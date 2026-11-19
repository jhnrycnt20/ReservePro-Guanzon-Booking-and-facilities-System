@php
    $authShowcaseSlides = [
        'images/rooms/cottage/Sunlit Picnic Pavilion Courtyard.png',
        'images/rooms/cabana/Sunset Modern Bungalow Unit 4.png',
        'images/rooms/cabana/Modern Bungalow Row at Sunset.png',
        'images/rooms/villa/01-exterior-evening.jpg',
        'images/rooms/beach-house/01-exterior.jpg',
        'images/rooms/suite/Vivid Resort Suites Under Blue Skies.png',
        'images/rooms/cottage/Tree-Lined Pavilion Walkway.png',
    ];
@endphp
<div class="rp-login-reference-slides" style="--rp-slide-count: {{ count($authShowcaseSlides) }};">
    @foreach ($authShowcaseSlides as $slide)
        <div class="rp-login-reference-slide" style="background-image: url('{{ asset($slide) }}');"></div>
    @endforeach
</div>
