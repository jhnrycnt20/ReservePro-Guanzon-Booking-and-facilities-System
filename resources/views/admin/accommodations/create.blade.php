@extends('layouts.dashboard')

@section('title', 'Add Accommodation')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Add Accommodation')
@section('page_subtitle', 'Create a room or facility listing')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.accommodations.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number / Code</label>
                        <input type="text" name="number" class="form-control" value="{{ old('number') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="accommodation_type_id" class="form-select" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected(old('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', 2) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rate</label>
                        <input type="number" step="0.01" min="0" name="rate" class="form-control" value="{{ old('rate') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['available','reserved','occupied','maintenance','inactive'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'available') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Amenities</label>
                        <div class="row g-2">
                            @foreach($amenities as $amenity)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}" @checked(collect(old('amenities', []))->contains($amenity->id))>
                                        <label class="form-check-label" for="amenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 form-check ms-1">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', true))>
                        <label class="form-check-label" for="is_active">Active listing</label>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-rp-primary">Create Accommodation</button>
                    <a href="{{ route('admin.accommodations.index') }}" class="btn btn-rp-soft">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
