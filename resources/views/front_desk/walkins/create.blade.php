@extends('layouts.dashboard')

@section('title', 'Walk-in Booking')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Handle Walk-in')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card rp-walkin-page">
    <div class="rp-walkin-modal-heading mb-4">
        <span class="rp-walkin-modal-icon" aria-hidden="true"><i class="bi bi-calendar2-plus"></i></span>
        <div>
            <h2 class="h5 mb-0">New Walk-in Booking</h2>
            <p class="rp-walkin-modal-sub mb-0">Create a new reservation for a walk-in guest.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('front_desk.walkins.store') }}" class="rp-walkin-modal" data-rp-walkin-form data-rp-availability-form data-occupied-url="{{ url('/accommodations') }}/__ACCOMMODATION__/occupied-dates" data-rp-no-auto-submit novalidate>
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-person me-1" aria-hidden="true"></i>Guest name</label>
                <input type="text" name="guest_name" class="form-control @error('guest_name') is-invalid @enderror" value="{{ old('guest_name') }}" required>
                @error('guest_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-telephone me-1" aria-hidden="true"></i>Contact number</label>
                <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" required>
                @error('contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label"><i class="bi bi-house-door me-1" aria-hidden="true"></i>Accommodation</label>
                <select name="accommodation_id" class="form-select" data-rp-capacity-select required>
                    @foreach($accommodations as $item)
                        <option value="{{ $item->id }}" data-capacity="{{ $item->capacity }}" data-rate="{{ $item->rate }}" @selected(old('accommodation_id') == $item->id)>
                            {{ $item->name }} — ₱{{ number_format($item->rate, 2) }} (cap {{ $item->capacity }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Check-in</label>
                <div class="rp-avail-input-wrap" data-rp-open-calendar>
                    <input type="text" class="rp-avail-input" value="{{ old('check_in_date') ? \Carbon\Carbon::parse(old('check_in_date'))->format('m/d/Y') : now()->format('m/d/Y') }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                    <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                </div>
                <input type="hidden" name="check_in_date" value="{{ old('check_in_date', now()->toDateString()) }}" data-stay-check-in required>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Check-out</label>
                <div class="rp-avail-input-wrap" data-rp-open-calendar>
                    <input type="text" class="rp-avail-input" value="{{ old('check_out_date') ? \Carbon\Carbon::parse(old('check_out_date'))->format('m/d/Y') : now()->addDay()->format('m/d/Y') }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                    <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                </div>
                <input type="hidden" name="check_out_date" value="{{ old('check_out_date', now()->addDay()->toDateString()) }}" data-stay-check-out required>
            </div>
            <div class="col-12">
                <div class="rp-walkin-total-banner">
                    <div>
                        <div class="rp-walkin-total-label">Full stay total</div>
                        <div class="rp-walkin-total-value" data-rp-walkin-total>—</div>
                    </div>
                    <div class="rp-walkin-total-hint">room rate × nights · must pay in full · Cash + GCash split allowed</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label">Adults</label>
                <input type="number" min="1" name="adults" class="form-control @error('adults') is-invalid @enderror" value="{{ old('adults', 1) }}" data-rp-guest-adults required>
                @error('adults')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label">Children</label>
                <input type="number" min="0" name="children" class="form-control" value="{{ old('children', 0) }}" data-rp-guest-children>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label">Guests</label>
                <input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', (int) old('adults', 1) + (int) old('children', 0)) }}" data-rp-guest-total readonly required>
                <div class="text-danger small d-none" data-rp-capacity-error>Exceeds capacity.</div>
            </div>
            <div class="col-12">
                <label class="form-label"><i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Special requests</label>
                <textarea name="special_requests" class="form-control" rows="2" placeholder="Optional notes for the stay…">{{ old('special_requests') }}</textarea>
            </div>
            @include('partials.walkin-payments')
        </div>
        <div class="rp-walkin-modal-footer mt-4">
            <a href="{{ route('front_desk.reservations.index') }}" class="btn btn-rp-soft">Cancel</a>
            <button type="submit" class="btn btn-rp-primary">Create Walk-in Booking</button>
        </div>
    </form>
</div>
@endsection

@include('partials.availability-calendar')
