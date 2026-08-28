@extends('layouts.dashboard')

@section('title', 'Walk-in Booking')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Handle Walk-in')
@section('page_subtitle', 'Register guest, create reservation, take payment, then check in')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card">
    <form method="POST" action="{{ route('front_desk.walkins.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Guest name</label>
                <input type="text" name="guest_name" class="form-control" value="{{ old('guest_name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact number</label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Accommodation</label>
                <select name="accommodation_id" class="form-select" required>
                    @foreach($accommodations as $item)
                        <option value="{{ $item->id }}" @selected(old('accommodation_id') == $item->id)>
                            {{ $item->name }} — ₱{{ number_format($item->rate, 2) }} (cap {{ $item->capacity }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Check-in</label>
                <input type="date" name="check_in_date" data-stay-check-in class="form-control" value="{{ old('check_in_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Check-out</label>
                <input type="date" name="check_out_date" data-stay-check-out class="form-control" value="{{ old('check_out_date', now()->addDay()->toDateString()) }}" min="{{ now()->addDay()->toDateString() }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Adults</label>
                <input type="number" min="1" name="adults" class="form-control" value="{{ old('adults', 1) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Children</label>
                <input type="number" min="0" name="children" class="form-control" value="{{ old('children', 0) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Guests</label>
                <input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', 1) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Special requests</label>
                <textarea name="special_requests" class="form-control" rows="2">{{ old('special_requests') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment amount (optional)</label>
                <input type="number" step="0.01" min="0" name="payment_amount" class="form-control" value="{{ old('payment_amount') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment method</label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Reference number</label>
                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
            </div>
            <div class="col-12 form-check ms-1">
                <input class="form-check-input" type="checkbox" name="auto_approve" value="1" id="auto_approve" checked>
                <label class="form-check-label" for="auto_approve">Auto-approve walk-in reservation</label>
            </div>
            <div class="col-12 form-check ms-1">
                <input class="form-check-input" type="checkbox" name="auto_check_in" value="1" id="auto_check_in">
                <label class="form-check-label" for="auto_check_in">Check-in immediately after payment verification</label>
            </div>
        </div>
        <button class="btn btn-rp-primary mt-3">Create Walk-in Booking</button>
    </form>
</div>
@endsection
