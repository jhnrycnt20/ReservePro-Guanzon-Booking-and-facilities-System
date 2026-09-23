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
            </div>
            <div class="col-md-6">
                <label class="form-label">Number / Code</label>
                <input type="text" name="number" class="form-control" value="{{ old('number') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="accommodation_type_id" class="form-select" required>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(old('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacity</label>
                <input type="number" min="1" name="capacity" class="form-control rp-no-spinner" value="{{ old('capacity', 2) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rate</label>
                <input type="number" step="0.01" min="0" name="rate" class="form-control rp-no-spinner" value="{{ old('rate') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Cover image</label>
            <input type="file" name="image" class="form-control rp-field-narrow" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label">Gallery images</label>
            <input type="file" name="gallery[]" class="form-control rp-field-narrow" accept="image/*" multiple>
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

                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    @foreach(\App\Enums\AccommodationStatus::manualValues() as $status)
                        <option value="{{ $status }}" @selected(old('status', 'available') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>

                <div class="mt-3">
                    <label class="form-label">Active listing</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', true))>Active</option>
                        <option value="0" @selected(! old('is_active', true))>Inactive</option>
                    </select>
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
})();
</script>
@endpush
