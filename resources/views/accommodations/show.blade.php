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
<div class="container py-4">
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card">
            <img class="w-100 rounded-3 mb-3" style="max-height:360px;object-fit:cover;" src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}">
            <div class="d-flex gap-2 mb-3">
                <x-status-badge :status="$accommodation->status" />
                <span class="badge text-bg-light">Capacity: {{ $accommodation->capacity }}</span>
            </div>
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
    <div class="col-lg-5">
        <div class="rp-card">
            <h2 class="h5 mb-3">Check Availability</h2>
            <form method="GET" action="{{ route('accommodations.availability', $accommodation) }}" class="mb-3">
                <div class="mb-3">
                    <label class="form-label">Check-in</label>
                    <input type="date" name="check_in" class="form-control" value="{{ request('check_in', old('check_in_date')) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Check-out</label>
                    <input type="date" name="check_out" class="form-control" value="{{ request('check_out', old('check_out_date')) }}" required>
                </div>
                <button class="btn btn-rp-soft w-100">Check Availability</button>
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
@endsection
