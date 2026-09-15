@extends('layouts.public')

@section('title', $accommodation->name)

@section('content')
<div class="container rp-public-page-top pb-4">

<a href="{{ route('accommodations.browse') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Accommodations</a>

@include('partials.booking-tracker', ['activeStep' => 'dates'])

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-cottage-card rp-cottage-card--static mb-4">
            @php $galleryUrls = $accommodation->gallery_urls; @endphp
            <div class="rp-cottage-media">
                <button
                    type="button"
                    class="rp-cottage-media-open border-0 bg-transparent p-0 w-100 text-start"
                    data-rp-lightbox-open
                    data-rp-lightbox-index="0"
                    aria-label="Open photo gallery"
                >
                    <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}" id="rpAccommodationMainImage">
                </button>
                <button
                    type="button"
                    class="rp-view-full-image-btn"
                    data-rp-lightbox-open
                    data-rp-lightbox-index="0"
                    aria-label="View full image"
                >
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
            </div>
            @if(count($galleryUrls) > 1)
                <div class="rp-accommodation-gallery">
                    @foreach($galleryUrls as $index => $url)
                        <button
                            type="button"
                            class="rp-accommodation-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}"
                            data-rp-gallery-src="{{ $url }}"
                            data-rp-lightbox-open
                            data-rp-lightbox-index="{{ $index }}"
                            aria-label="Show photo {{ $index + 1 }}"
                        >
                            <img src="{{ $url }}" alt="{{ $accommodation->name }} photo {{ $index + 1 }}">
                        </button>
                    @endforeach
                </div>
            @endif
            <div class="rp-cottage-card-body">
                <div class="rp-cottage-title">{{ $accommodation->name }}</div>
                @php $typeName = $accommodation->accommodationType->name ?? $accommodation->type->name ?? null; @endphp
                @if($typeName && strcasecmp($typeName, $accommodation->name) !== 0)
                    <div class="rp-cottage-subtitle">{{ $typeName }}</div>
                @endif
                <div class="rp-cottage-row">
                    <span>Rate</span>
                    <span>₱{{ number_format($accommodation->rate, 0) }}</span>
                </div>
                <div class="rp-cottage-row">
                    <span>Max guests</span>
                    <span>{{ $accommodation->capacity }}</span>
                </div>
            </div>
        </div>

        <div class="rp-flow-card">
            <p>{{ $accommodation->description }}</p>
            <h3 class="h6">Amenities</h3>
            <div class="d-flex flex-wrap gap-2">
                @forelse($accommodation->amenities as $amenity)
                    <span class="badge text-bg-light border">{{ $amenity->name }}</span>
                @empty
                    <span class="text-muted">No amenities listed.</span>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        @php
            $checkInDisplay = request('check_in') ? \Carbon\Carbon::parse(request('check_in'))->format('M j, Y') : '';
            $checkOutDisplay = request('check_out') ? \Carbon\Carbon::parse(request('check_out'))->format('M j, Y') : '';
        @endphp
        <div class="rp-avail-card">
            <form method="GET" action="{{ route('accommodations.availability', $accommodation) }}" id="rpAvailabilityForm"
                  data-rp-availability-form
                  data-occupied-url="{{ route('accommodations.occupied-dates', $accommodation) }}">
                <div class="rp-avail-fields">
                    <div class="rp-avail-field">
                        <label class="rp-avail-label">Check-in</label>
                        <div class="rp-avail-input-wrap" data-rp-open-calendar>
                            <input type="text" class="rp-avail-input" value="{{ $checkInDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                            <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                        </div>
                        <input type="hidden" name="check_in" value="{{ request('check_in', old('check_in_date')) }}" data-stay-check-in>
                    </div>
                    <div class="rp-avail-field">
                        <label class="rp-avail-label">Check-out</label>
                        <div class="rp-avail-input-wrap" data-rp-open-calendar>
                            <input type="text" class="rp-avail-input" value="{{ $checkOutDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                            <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                        </div>
                        <input type="hidden" name="check_out" value="{{ request('check_out', old('check_out_date')) }}" data-stay-check-out>
                    </div>
                </div>
                <button type="button" class="rp-avail-btn-primary" data-rp-show-calendar>Change Date</button>
            </form>

            @isset($available)
                @if($available)
                    <div class="rp-avail-message">
                        Available for selected dates.
                    </div>
                    {{-- TEMP: always shows Fill Reservation Form (login requirement bypassed for now, see routes/web.php) --}}
                    <a href="{{ route('guest.bookings.create', ['accommodation_id' => $accommodation->id, 'check_in' => request('check_in'), 'check_out' => request('check_out')]) }}" class="rp-avail-btn-secondary">Fill Reservation Form</a>
                @else
                    <div class="rp-avail-message rp-avail-message--warning">Not available. Choose another room or different dates.</div>
                @endif
            @endisset
        </div>
    </div>
</div>

</div>

@include('partials.availability-calendar')

@if(count($accommodation->gallery_urls) > 0)
<div class="modal fade" id="rpAccommodationLightbox" tabindex="-1" aria-labelledby="rpAccommodationLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rp-lightbox-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h6 text-white" id="rpAccommodationLightboxLabel">{{ $accommodation->name }}</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-lightbox-stage">
                    @if(count($accommodation->gallery_urls) > 1)
                        <button type="button" class="rp-lightbox-nav rp-lightbox-prev" data-rp-lightbox-prev aria-label="Previous photo">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    @endif
                    <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}" id="rpLightboxImage" class="rp-lightbox-image">
                    @if(count($accommodation->gallery_urls) > 1)
                        <button type="button" class="rp-lightbox-nav rp-lightbox-next" data-rp-lightbox-next aria-label="Next photo">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    @endif
                </div>
                <div class="rp-lightbox-meta">
                    <span id="rpLightboxCounter">1 / {{ count($accommodation->gallery_urls) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const images = @json($accommodation->gallery_urls);
    if (!images.length) return;

    const mainImage = document.getElementById('rpAccommodationMainImage');
    const lightboxEl = document.getElementById('rpAccommodationLightbox');
    const lightboxImage = document.getElementById('rpLightboxImage');
    const counterEl = document.getElementById('rpLightboxCounter');
    let index = 0;

    const setActiveThumb = (activeIndex) => {
        document.querySelectorAll('[data-rp-lightbox-index]').forEach((thumb) => {
            const thumbIndex = Number(thumb.dataset.rpLightboxIndex);
            thumb.classList.toggle('is-active', thumbIndex === activeIndex);
        });
    };

    const showImage = (nextIndex, { syncMain = true } = {}) => {
        index = (nextIndex + images.length) % images.length;
        const src = images[index];

        if (lightboxImage) {
            lightboxImage.src = src;
        }
        if (counterEl) {
            counterEl.textContent = `${index + 1} / ${images.length}`;
        }
        if (syncMain && mainImage) {
            mainImage.src = src;
        }
        setActiveThumb(index);
    };

    document.querySelectorAll('[data-rp-lightbox-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const startIndex = Number(button.dataset.rpLightboxIndex || 0);
            showImage(Number.isFinite(startIndex) ? startIndex : 0);
            if (lightboxEl && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(lightboxEl).show();
            }
        });
    });

    document.querySelector('[data-rp-lightbox-prev]')?.addEventListener('click', () => showImage(index - 1));
    document.querySelector('[data-rp-lightbox-next]')?.addEventListener('click', () => showImage(index + 1));

    document.addEventListener('keydown', (event) => {
        if (!lightboxEl?.classList.contains('show')) return;
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showImage(index - 1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showImage(index + 1);
        }
    });
})();
</script>
@endpush
