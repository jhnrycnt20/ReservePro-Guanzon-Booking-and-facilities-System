@extends('layouts.dashboard')

@section('title', 'Edit Accommodation')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit Accommodation')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.accommodations.update', $accommodation) }}" enctype="multipart/form-data" class="rp-accommodation-form" id="rpAccommodationForm">
    @csrf
    @method('PUT')

    <div class="rp-flow-card" data-rp-acc-step="1">
        <div class="row g-3 mb-1">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $accommodation->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Number / Code</label>
                <input type="text" name="number" class="form-control" value="{{ old('number', $accommodation->number) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="accommodation_type_id" class="form-select" required>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('accommodation_type_id', $accommodation->accommodation_type_id) == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacity</label>
                <input type="number" min="1" name="capacity" class="form-control rp-no-spinner" value="{{ old('capacity', $accommodation->capacity) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rate</label>
                <input type="number" step="0.01" min="0" name="rate" class="form-control rp-no-spinner" value="{{ old('rate', $accommodation->rate) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Availability</label>
                @php
                    $statusLocked = $accommodation->isStatusLocked();
                    $current = $accommodation->status instanceof \BackedEnum
                        ? $accommodation->status->value
                        : (string) $accommodation->status;
                    $selected = old('status', $current);
                    if (! in_array($selected, \App\Enums\AccommodationStatus::manualValues(), true)) {
                        $selected = \App\Enums\AccommodationStatus::Available->value;
                    }
                @endphp
                @if($statusLocked)
                    <input type="hidden" name="status" value="{{ $current }}">
                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $current)) }}" disabled>
                    <div class="form-text text-warning">{{ $accommodation->statusLockReason() }}</div>
                @else
                    <select name="status" class="form-select" required>
                        @foreach(\App\Enums\AccommodationStatus::manualLabels() as $value => $label)
                            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Cover image</label>
            <input type="file" name="image" class="form-control rp-field-narrow" accept="image/*">
            @if($accommodation->image)
                <div class="mt-2">
                    <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}" class="img-thumbnail" style="max-height: 90px;">
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Gallery images</label>
            <input type="file" name="gallery[]" class="form-control rp-field-narrow" accept="image/*" multiple>
            @if($accommodation->gallery_items)
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($accommodation->gallery_items as $index => $item)
                        <div class="rp-gallery-thumb-check">
                            <img src="{{ $item['url'] }}" alt="Gallery photo" class="img-fluid rounded border">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="{{ $item['path'] }}" id="removeGallery{{ $index }}">
                                <label class="form-check-label small" for="removeGallery{{ $index }}">Remove</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('admin.accommodations.index') }}" class="rp-avail-btn-secondary rp-avail-btn-secondary--inline">Cancel</a>
            <button type="button" class="rp-avail-btn-primary rp-avail-btn-primary--inline" data-rp-acc-next="2">Next</button>
        </div>
    </div>

    <div class="rp-flow-card d-none" data-rp-acc-step="2">
        <div class="row g-3 align-items-start">
            <div class="col-6">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control mb-3" rows="3">{{ old('description', $accommodation->description) }}</textarea>

                <div class="mt-0">
                    <label class="form-label">Active listing</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', $accommodation->is_active))>Active</option>
                        <option value="0" @selected(! old('is_active', $accommodation->is_active))>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <label class="form-label">Amenities</label>
                @php $selected = collect(old('amenities', $accommodation->amenities->pluck('id')->all())); @endphp
                <div class="rp-amenities-box">
                    @foreach($amenities as $amenity)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" @checked($selected->contains($amenity->id))>
                            <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="rp-avail-btn-secondary rp-avail-btn-secondary--inline" data-rp-acc-back="1">Back</button>
            <button type="submit" class="rp-avail-btn-primary rp-avail-btn-primary--inline">Save Changes</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('rpAccommodationForm');
    if (!form) return;

    const steps = form.querySelectorAll('[data-rp-acc-step]');

    const goToStep = (step) => {
        steps.forEach((card) => {
            card.classList.toggle('d-none', card.dataset.rpAccStep !== String(step));
        });
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    form.querySelectorAll('[data-rp-acc-next]').forEach((btn) => {
        btn.addEventListener('click', () => goToStep(btn.dataset.rpAccNext));
    });
    form.querySelectorAll('[data-rp-acc-back]').forEach((btn) => {
        btn.addEventListener('click', () => goToStep(btn.dataset.rpAccBack));
    });
})();
</script>
@endpush
