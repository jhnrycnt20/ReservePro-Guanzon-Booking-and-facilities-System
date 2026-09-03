@extends(auth()->check() ? 'layouts.dashboard' : 'layouts.public')

@section('title', $accommodation->name)
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', $accommodation->name)
@section('page_subtitle', ($accommodation->accommodationType->name ?? 'Accommodation').' · ₱'.number_format($accommodation->rate, 2).'/night')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
@if(!auth()->check())
<div class="container rp-public-page-top pb-4">
@endif

<a href="{{ route('accommodations.browse') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Accommodations</a>

@include('partials.booking-tracker', ['activeStep' => 'dates'])

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-cottage-card rp-cottage-card--static mb-4">
            <div class="rp-cottage-media">
                <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}">
                <a href="{{ $accommodation->image_url }}" target="_blank" rel="noopener" class="rp-view-full-image-btn" aria-label="View full image">
                    <i class="bi bi-arrows-fullscreen"></i>
                </a>
            </div>
            <div class="rp-cottage-card-body">
                <div class="rp-cottage-title">{{ $accommodation->name }}</div>
                <div class="rp-cottage-subtitle">{{ $accommodation->accommodationType->name ?? 'Accommodation' }}</div>
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
            <h2 class="rp-avail-heading">Check Availability</h2>
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
                <button type="button" class="rp-avail-btn-primary" data-rp-show-calendar>Check Availability</button>
            </form>

            @isset($available)
                @if($available)
                    <div class="rp-avail-message">
                        <i class="bi bi-check-circle-fill"></i> Available for selected dates.
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

@if(!auth()->check())
</div>
@endif

@include('partials.availability-calendar')
@endsection
