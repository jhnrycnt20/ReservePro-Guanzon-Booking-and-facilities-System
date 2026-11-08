@extends('layouts.public')

@section('title', $accommodation->baseDisplayName())
@section('body_class', 'rp-page-accommodation-detail')

@section('content')
<div class="container rp-public-page-top rp-accommodation-show-page pb-4">

@php
    $backQuery = array_filter([
        'type' => request('type'),
        'check_in' => request('check_in'),
        'check_out' => request('check_out'),
    ], fn ($value) => $value !== null && $value !== '');
@endphp
<a href="{{ route('accommodations.browse', $backQuery) }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Accommodations</a>

<div class="row g-4 rp-accommodation-detail-layout">
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
                @php
                    $displayName = $accommodation->baseDisplayName();
                    $typeName = $accommodation->accommodationType->name ?? $accommodation->type->name ?? null;
                @endphp
                <h2 class="rp-cottage-title">{{ $displayName }}</h2>
                <div class="rp-accommodation-meta-line">
                    @if($typeName)
                        <span>{{ $typeName }}</span>
                    @endif
                    <span>{{ $accommodation->capacity }} {{ \Illuminate\Support\Str::plural('guest', $accommodation->capacity) }}</span>
                </div>
            </div>
            <div class="rp-accommodation-highlights">
                @if(filled($accommodation->description))
                    <p class="rp-accommodation-description">{{ $accommodation->description }}</p>
                @endif
                <div class="rp-accommodation-amenities-heading">
                    <div><h3>Amenities</h3></div>
                </div>
                <div class="rp-accommodation-amenities-list" aria-label="Amenities">
                    @forelse($accommodation->amenities as $amenity)
                        <span class="rp-accommodation-amenity"><i class="bi bi-check2" aria-hidden="true"></i>{{ $amenity->name }}</span>
                    @empty
                        <span class="rp-accommodation-no-amenities">Amenities will be listed soon.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        @php
            $checkInDisplay = request('check_in') ? \Carbon\Carbon::parse(request('check_in'))->format('M j, Y') : '';
            $checkOutDisplay = request('check_out') ? \Carbon\Carbon::parse(request('check_out'))->format('M j, Y') : '';
            $unitOptions = $units ?? collect();
        @endphp
        <div class="rp-avail-card rp-accommodation-booking-card">
            <div class="rp-accommodation-booking-heading">
                <div>
                    <h1><span>&#8369;{{ number_format($accommodation->rate, 0) }}</span><small>/ night</small></h1>
                    <p>Select your dates</p>
                </div>
            </div>
            <form method="GET" action="{{ route('guest.bookings.create') }}" id="rpAvailabilityForm"
                  data-rp-availability-form
                  data-rp-proceed-only
                  data-occupied-url="{{ route('accommodations.occupied-dates', $accommodation) }}"
                  data-rp-units='@json($unitOptions)'
                  data-rp-selected-unit="{{ $accommodation->id }}"
                  data-rp-family="{{ $accommodation->familyKey() }}">
                <input type="hidden" name="accommodation_id" value="{{ $accommodation->id }}" data-rp-selected-accommodation>
                @if($errors->has('check_in') || $errors->has('check_out'))
                    <div class="rp-date-validation-message is-visible" data-rp-date-error role="alert">
                        {{ $errors->first('check_in') ?: $errors->first('check_out') }}
                    </div>
                @else
                    <div class="rp-date-validation-message" data-rp-date-error role="alert" aria-live="assertive" hidden></div>
                @endif
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
                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="rp-avail-btn-primary rp-accommodation-proceed-button">Book Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

@include('partials.availability-calendar')

@if(count($accommodation->gallery_urls) > 0)
<div class="modal fade" id="rpAccommodationLightbox" tabindex="-1" aria-labelledby="rpAccommodationLightboxLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered rp-lightbox-dialog">
        <div class="modal-content rp-lightbox-modal">
            <div class="modal-header border-0">
                <h2 class="modal-title rp-lightbox-title" id="rpAccommodationLightboxLabel">{{ $accommodation->name }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rp-lightbox-stage" id="rpLightboxStage" style="--rp-lightbox-bg: url('{{ $accommodation->image_url }}');">
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
                        <span class="rp-lightbox-counter" id="rpLightboxCounter">1 / {{ count($accommodation->gallery_urls) }}</span>
                    @endif
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
    const lightboxStage = document.getElementById('rpLightboxStage');
    const counterEl = document.getElementById('rpLightboxCounter');
    let index = 0;

    const setActiveThumb = (activeIndex) => {
        document.querySelectorAll('[data-rp-lightbox-index]').forEach((thumb) => {
            const thumbIndex = Number(thumb.dataset.rpLightboxIndex);
            thumb.classList.toggle('is-active', thumbIndex === activeIndex);
        });
        document.querySelector(`.rp-accommodation-gallery-thumb[data-rp-lightbox-index="${activeIndex}"]`)
            ?.scrollIntoView({
                block: 'nearest',
                inline: 'nearest',
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });
    };

    const showImage = (nextIndex, { syncMain = true } = {}) => {
        index = (nextIndex + images.length) % images.length;
        const src = images[index];

        if (lightboxImage) {
            lightboxImage.src = src;
        }
        lightboxStage?.style.setProperty('--rp-lightbox-bg', `url("${src}")`);
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
            if (button.classList.contains('rp-accommodation-gallery-thumb')) return;
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

(() => {
    const form = document.getElementById('rpAvailabilityForm');
    if (!form) return;

    const checkIn = form.querySelector('[data-stay-check-in]');
    const checkOut = form.querySelector('[data-stay-check-out]');
    const checkInDisplay = form.querySelector('[data-rp-date-display="check_in"]');
    const checkOutDisplay = form.querySelector('[data-rp-date-display="check_out"]');
    const errorBox = form.querySelector('[data-rp-date-error]');
    const fields = [checkInDisplay, checkOutDisplay].filter(Boolean);

    const validateDates = () => {
        const start = checkIn?.value || '';
        const end = checkOut?.value || '';
        let message = '';

        if (!start || !end) {
            message = 'Choose both check-in and check-out dates to continue.';
        } else if (end <= start) {
            message = 'Check-out must be later than check-in.';
        }

        if (errorBox) {
            errorBox.textContent = message;
            errorBox.hidden = !message;
            errorBox.classList.toggle('is-visible', Boolean(message));
        }

        fields.forEach((field, index) => {
            const missing = index === 0 ? !start : !end;
            const invalid = missing || (Boolean(start && end) && end <= start);
            field.setAttribute('aria-invalid', String(Boolean(message && invalid)));
            field.closest('.rp-avail-input-wrap')?.classList.toggle('is-date-invalid', Boolean(message && invalid));
        });

        return !message;
    };

    form.addEventListener('submit', (event) => {
        if (validateDates()) return;
        event.preventDefault();
        const firstMissingField = !checkIn?.value ? checkInDisplay : checkOutDisplay;
        firstMissingField?.focus();
    });

    [checkIn, checkOut].filter(Boolean).forEach((field) => {
        field.addEventListener('change', validateDates);
    });
})();
</script>
@endpush
