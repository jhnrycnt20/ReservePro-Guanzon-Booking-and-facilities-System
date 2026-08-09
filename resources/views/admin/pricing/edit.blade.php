@extends('layouts.dashboard')

@section('title', 'Edit Pricing')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit Pricing')
@section('page_subtitle', $pricing->name)
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.pricing.update', $pricing) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $pricing->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accommodation</label>
                        <select name="accommodation_id" class="form-select" required>
                            @foreach($accommodations as $item)
                                <option value="{{ $item->id }}" @selected(old('accommodation_id', $pricing->accommodation_id) == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount', $pricing->amount) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($pricing->start_date)->format('Y-m-d') ?? $pricing->start_date) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($pricing->end_date)->format('Y-m-d') ?? $pricing->end_date) }}">
                    </div>
                    <div class="col-12 form-check ms-1">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $pricing->is_active))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-rp-primary">Save Changes</button>
                    <a href="{{ route('admin.pricing.index') }}" class="btn btn-rp-soft">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
