@extends('layouts.dashboard')

@section('title', 'Reservation Form')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Reservation Form')
@section('page_subtitle', 'Submit a reservation for review by front desk staff')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card">
            <form method="POST" action="{{ route('guest.bookings.store') }}">
                @csrf
                <input type="hidden" name="accommodation_id" value="{{ $accommodation->id }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guest name</label>
                        <input type="text" name="guest_name" class="form-control" value="{{ old('guest_name', auth()->user()->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accommodation</label>
                        <input type="text" class="form-control" value="{{ $accommodation->name }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-in date</label>
                        <input type="date" name="check_in_date" data-calc-check-in data-stay-check-in class="form-control" value="{{ old('check_in_date', $checkIn ?? request('check_in')) }}" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-out date</label>
                        <input type="date" name="check_out_date" data-calc-check-out data-stay-check-out class="form-control" value="{{ old('check_out_date', $checkOut ?? request('check_out')) }}" min="{{ now()->addDay()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Adults</label>
                        <input type="number" min="1" name="adults" class="form-control" value="{{ old('adults', 1) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Children</label>
                        <input type="number" min="0" name="children" class="form-control" value="{{ old('children', 0) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Number of guests</label>
                        <input type="number" min="1" max="{{ $accommodation->capacity }}" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', 1) }}" required>
                        <div class="form-text">Max capacity: {{ $accommodation->capacity }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Special requests</label>
                        <textarea name="special_requests" class="form-control" rows="3">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    Estimated total (client preview only): <strong data-calc-total data-calc-rate="{{ $accommodation->rate }}">—</strong>
                    <div class="small">Final amount is calculated and stored by the server.</div>
                </div>

                <button class="btn btn-rp-primary mt-3">Submit Reservation</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-card">
            <h2 class="h5">{{ $accommodation->name }}</h2>
            <p class="text-muted">{{ $accommodation->description }}</p>
            <div><strong>₱{{ number_format($accommodation->rate, 2) }}</strong> / night</div>
            <div class="mt-2"><x-status-badge :status="$accommodation->status" /></div>
        </div>
    </div>
</div>
@endsection
