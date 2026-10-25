@extends('layouts.dashboard')

@section('title', 'Add Accommodation')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Add Accommodation')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.accommodations.store') }}" enctype="multipart/form-data" class="rp-accommodation-form" id="rpAccommodationForm">
    @csrf

    <div class="rp-flow-card" data-rp-acc-step="1">
        <div class="row g-3 mb-1">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                <x-accommodation-field-error field="name" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Number</label>
                <select name="number" class="form-select" required>
                    <option value="" disabled @selected(old('number') === null)>Select a number</option>
                    @foreach(range(1, 10) as $number)
                        <option value="{{ $number }}" @selected((string) old('number') === (string) $number)>{{ $number }}</option>
                    @endforeach
                </select>
                <x-accommodation-field-error field="number" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="accommodation_type_id" class="form-select" required>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <x-accommodation-field-error field="accommodation_type_id" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacity</label>
                <input type="number" min="1" max="50" name="capacity" class="form-control rp-no-spinner" value="{{ old('capacity', 2) }}" required>
                <x-accommodation-field-error field="capacity" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Rate</label>
                <input type="number" step="0.01" min="0" max="999999.99" name="rate" class="form-control rp-no-spinner" value="{{ old('rate') }}" required>
                <x-accommodation-field-error field="rate" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Availability</label>
                <select name="status" class="form-select" required>
                    @foreach(\App\Enums\AccommodationStatus::manualLabels() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'available') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-accommodation-field-error field="status" />
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Cover image</label>
            <input type="file" name="image" class="form-control rp-field-narrow" accept=".jpg,.jpeg,.png,.webp">
            <x-accommodation-field-error field="image" />
        </div>

        <div class="mb-3">
            <label class="form-label">Gallery images</label>
            <input type="file" name="gallery[]" class="form-control rp-field-narrow" accept=".jpg,.jpeg,.png,.webp" multiple>
            <x-accommodation-field-error field="gallery" />
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
                <textarea name="description" class="form-control mb-3" rows="3">{{ old('description') }}</textarea>
                <x-accommodation-field-error field="description" />

                <div class="mt-0">
                    <label class="form-label">Active listing</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', true))>Active</option>
                        <option value="0" @selected(! old('is_active', true))>Inactive</option>
                    </select>
                    <x-accommodation-field-error field="is_active" />
                </div>
            </div>
            <div class="col-6">
                <label class="form-label">Amenities</label>
                <div class="rp-amenities-box">
                    @forelse($amenities as $amenity)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" @checked(collect(old('amenities', []))->contains($amenity->id))>
                            <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                        </div>
                    @empty
                        <div class="text-muted small">No amenities yet. Add some under Amenities first.</div>
                    @endforelse
                </div>
                <x-accommodation-field-error field="amenities" />
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="rp-avail-btn-secondary rp-avail-btn-secondary--inline" data-rp-acc-back="1">Back</button>
            <button type="submit" class="rp-avail-btn-primary rp-avail-btn-primary--inline">Create Accommodation</button>
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

    const firstErrorStep = form.querySelector('.invalid-feedback')?.closest('[data-rp-acc-step]');
    if (firstErrorStep) goToStep(firstErrorStep.dataset.rpAccStep);
})();
</script>
@endpush
