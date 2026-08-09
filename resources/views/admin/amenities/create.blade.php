@extends('layouts.dashboard')

@section('title', 'Add Amenity')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Add Amenity')
@section('page_subtitle', 'Create a facility feature')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.amenities.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon') }}" placeholder="e.g. bi-wifi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-rp-primary">Create Amenity</button>
                    <a href="{{ route('admin.amenities.index') }}" class="btn btn-rp-soft">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
