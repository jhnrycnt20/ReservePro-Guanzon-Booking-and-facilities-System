@extends('layouts.dashboard')

@section('title', 'Edit Type')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit Type')
@section('page_subtitle', $type->name)
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.types.update', $type) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $type->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $type->description) }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-rp-primary">Save Changes</button>
                    <a href="{{ route('admin.types.index') }}" class="btn btn-rp-soft">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
