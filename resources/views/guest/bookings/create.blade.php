@extends('layouts.public')

@section('title', 'Reservation Form')

@section('content')
<div class="container rp-public-page-top pb-4">

<a href="{{ route('accommodations.show', ['accommodation' => $accommodation->id, 'check_in' => $checkIn ?? request('check_in'), 'check_out' => $checkOut ?? request('check_out')]) }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back</a>

@include('partials.booking-tracker', ['activeStep' => 'guest'])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-flow-card">
            @php
                $checkInRaw = old('check_in_date', $checkIn ?? request('check_in'));
                $checkOutRaw = old('check_out_date', $checkOut ?? request('check_out'));
                $checkInDisplay = $checkInRaw ? \Carbon\Carbon::parse($checkInRaw)->format('M j, Y') : '';
                $checkOutDisplay = $checkOutRaw ? \Carbon\Carbon::parse($checkOutRaw)->format('M j, Y') : '';
            @endphp
            <form method="POST" action="{{ route('guest.bookings.store') }}" data-rp-availability-form data-occupied-url="{{ route('accommodations.occupied-dates', $accommodation) }}" data-rp-no-auto-submit>
                @csrf
                <input type="hidden" name="accommodation_id" value="{{ $accommodation->id }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guest name</label>
                        <input type="text" name="guest_name" class="form-control" value="{{ old('guest_name', auth()->user()->name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->phone ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accommodation</label>
                        <input type="text" class="form-control" value="{{ $accommodation->name }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-in date</label>
                        <div class="rp-avail-input-wrap" data-rp-open-calendar>
                            <input type="text" class="rp-avail-input" value="{{ $checkInDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                            <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                        </div>
                        <input type="hidden" name="check_in_date" value="{{ $checkInRaw }}" data-calc-check-in data-stay-check-in>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-out date</label>
                        <div class="rp-avail-input-wrap" data-rp-open-calendar>
                            <input type="text" class="rp-avail-input" value="{{ $checkOutDisplay }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                            <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                        </div>
                        <input type="hidden" name="check_out_date" value="{{ $checkOutRaw }}" data-calc-check-out data-stay-check-out>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Adults</label>
                        <input type="number" min="1" max="{{ $accommodation->capacity }}" name="adults" class="form-control @error('adults') is-invalid @enderror" value="{{ old('adults', 1) }}" data-rp-guest-adults required>
                        @error('adults')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Children</label>
                        <input type="number" min="0" max="{{ $accommodation->capacity }}" name="children" class="form-control @error('children') is-invalid @enderror" value="{{ old('children', 0) }}" data-rp-guest-children>
                        @error('children')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Number of guests</label>
                        <input type="number" min="1" max="{{ $accommodation->capacity }}" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', (int) old('adults', 1) + (int) old('children', 0)) }}" data-rp-guest-total data-rp-capacity="{{ $accommodation->capacity }}" readonly required>
                        <div class="form-text">Max capacity: {{ $accommodation->capacity }} (adults + children)</div>
                        <div class="text-danger small d-none" data-rp-capacity-error>Total guests cannot exceed capacity.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Special requests</label>
                        <textarea name="special_requests" class="form-control" rows="3">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-3">
                    Estimated total (client preview only): <strong data-calc-total data-calc-rate="{{ $accommodation->rate }}">—</strong>
                    <div class="small" data-rp-promo-estimate></div>
                    <div class="small">Final amount is calculated and stored by the server.</div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" required>
                    <label class="form-check-label small" for="agreeTerms">
                        I have read and agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#rpTermsModal" data-rp-terms-anchor="modal-cancellation-refund">Terms &amp; Conditions</a>
                    </label>
                </div>

                <button type="submit" class="rp-avail-btn-secondary mt-3">Submit Reservation</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-flow-card">
            <h2 class="h5">{{ $accommodation->name }}</h2>
            <p class="text-muted">{{ $accommodation->description }}</p>
            <div><strong>₱{{ number_format($accommodation->rate, 2) }}</strong> / night</div>
        </div>
    </div>
</div>

</div>

@include('partials.availability-calendar')
@endsection
