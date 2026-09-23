@extends('layouts.dashboard')

@section('title', 'View Accommodations')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'View Accommodations')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-card">
    <div class="mb-4">
        <div class="text-muted small">No. {{ $accommodation->number }}</div>
        <div class="fs-3 fw-bold text-body">{{ $accommodation->name }}</div>
        <div class="text-muted">{{ $accommodation->type->name ?? '—' }}</div>
    </div>

    @php $galleryUrls = collect($accommodation->gallery_urls)->take(5); @endphp
    @if($galleryUrls->isEmpty())
        <div class="text-muted mb-4">No images uploaded.</div>
    @else
        <div class="rp-accommodation-gallery-layout mb-4">
            <img src="{{ $galleryUrls->first() }}" alt="{{ $accommodation->name }}" class="rp-accommodation-gallery-main">
            @if($galleryUrls->count() > 1)
                <div class="rp-accommodation-gallery-side">
                    @foreach($galleryUrls->slice(1) as $imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $accommodation->name }}">
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Capacity</div>
                <div class="value">{{ $accommodation->capacity }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Rate</div>
                <div class="value">₱{{ number_format((float) $accommodation->rate, 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Listed</div>
                <div class="value">{{ $accommodation->is_active ? 'Yes' : 'No' }}</div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h6 mb-2">Description</h2>
    <p class="text-muted">{{ $accommodation->description ?: 'No description.' }}</p>

    <h2 class="h6 mb-2">Amenities</h2>
    <div class="d-flex flex-wrap gap-2">
        @forelse($accommodation->amenities as $amenity)
            <span class="badge text-bg-light border">{{ $amenity->name }}</span>
        @empty
            <span class="text-muted">No amenities listed.</span>
        @endforelse
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('admin.accommodations.index') }}" class="btn btn-rp-soft">Back</a>
        <a href="{{ route('admin.accommodations.edit', $accommodation) }}" class="btn btn-rp-primary">Edit</a>
    </div>
</div>
@endsection
