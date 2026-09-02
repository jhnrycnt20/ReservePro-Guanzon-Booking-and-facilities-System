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

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-cottage-card rp-cottage-card--static mb-4">
            <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}">
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
                <div class="rp-cottage-row">
                    <span>Status</span>
                    <span>{{ ucfirst($accommodation->status->value ?? $accommodation->status) }}</span>
                </div>
            </div>
        </div>

        <div class="rp-card">
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
        <div class="rp-card">
            <h2 class="h5 mb-3">Check Availability</h2>
            <form method="GET" action="{{ route('accommodations.availability', $accommodation) }}" class="mb-3" id="rpAvailabilityForm"
                  data-rp-availability-form
                  data-occupied-url="{{ route('accommodations.occupied-dates', $accommodation) }}">
                <div class="mb-3">
                    <label class="form-label">Check-in</label>
                    <input type="date" name="check_in" class="form-control" value="{{ request('check_in', old('check_in_date')) }}" min="{{ now()->toDateString() }}" data-stay-check-in data-rp-open-calendar readonly required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Check-out</label>
                    <input type="date" name="check_out" class="form-control" value="{{ request('check_out', old('check_out_date')) }}" min="{{ now()->addDay()->toDateString() }}" data-stay-check-out data-rp-open-calendar readonly required>
                </div>
                <button type="button" class="btn btn-rp-soft w-100 mb-2" data-rp-show-calendar>
                    Check in - Check out
                </button>
                <button type="button" class="btn btn-rp-primary w-100" data-rp-show-calendar>Check Availability</button>
            </form>

            @isset($available)
                @if($available)
                    <div class="alert alert-success">Available for selected dates.</div>
                    @auth
                        <a href="{{ route('guest.bookings.create', ['accommodation_id' => $accommodation->id, 'check_in' => request('check_in'), 'check_out' => request('check_out')]) }}" class="btn btn-rp-primary w-100">Fill Reservation Form</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-rp-primary w-100">Login to Reserve</a>
                    @endauth
                @else
                    <div class="alert alert-warning mb-0">Not available. Choose another room or different dates.</div>
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
