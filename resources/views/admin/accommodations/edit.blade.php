@extends('layouts.dashboard')

@section('title', 'Edit Accommodation')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit Accommodation')
@section('page_subtitle', $accommodation->name)
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.accommodations.update', $accommodation) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $accommodation->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number / Code</label>
                        <input type="text" name="number" class="form-control" value="{{ old('number', $accommodation->number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="accommodation_type_id" class="form-select" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(old('accommodation_type_id', $accommodation->accommodation_type_id) == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', $accommodation->capacity) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rate</label>
                        <input type="number" step="0.01" min="0" name="rate" class="form-control" value="{{ old('rate', $accommodation->rate) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['available','reserved','occupied','maintenance','inactive'] as $status)
                                @php $current = $accommodation->status instanceof \BackedEnum ? $accommodation->status->value : $accommodation->status; @endphp
                                <option value="{{ $status }}" @selected(old('status', $current) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if($accommodation->image)
                            <div class="mt-2">
                                <img src="{{ $accommodation->image_url }}" alt="{{ $accommodation->name }}" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $accommodation->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Amenities</label>
                        @php $selected = collect(old('amenities', $accommodation->amenities->pluck('id')->all())); @endphp
                        <div class="row g-2">
                            @foreach($amenities as $amenity)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" @checked($selected->contains($amenity->id))>
                                        <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 form-check ms-1">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $accommodation->is_active))>
                        <label class="form-check-label" for="is_active">Active listing</label>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-rp-primary">Save Changes</button>
                    <a href="{{ route('admin.accommodations.index') }}" class="btn btn-rp-soft">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
