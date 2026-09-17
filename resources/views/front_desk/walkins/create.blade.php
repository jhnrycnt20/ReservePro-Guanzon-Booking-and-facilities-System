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
    <form method="POST" action="{{ route('front_desk.walkins.store') }}" data-rp-payment-form data-rp-availability-form data-occupied-url="{{ url('/accommodations') }}/__ACCOMMODATION__/occupied-dates" data-rp-no-auto-submit novalidate>
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Guest name</label>
                <input type="text" name="guest_name" class="form-control @error('guest_name') is-invalid @enderror" value="{{ old('guest_name') }}" required>
                @error('guest_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact number</label>
                <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" required>
                @error('contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Accommodation</label>
                <select name="accommodation_id" class="form-select" data-rp-capacity-select required>
                    @foreach($accommodations as $item)
                        <option value="{{ $item->id }}" data-capacity="{{ $item->capacity }}" data-rate="{{ $item->rate }}" @selected(old('accommodation_id') == $item->id)>
                            {{ $item->name }} — ₱{{ number_format($item->rate, 2) }} (cap {{ $item->capacity }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Check-in</label>
                <div class="rp-avail-input-wrap" data-rp-open-calendar>
                    <input type="text" class="rp-avail-input" value="{{ old('check_in_date', now()->format('m/d/Y')) }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                    <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                </div>
                <input type="hidden" name="check_in_date" value="{{ old('check_in_date', now()->toDateString()) }}" data-stay-check-in required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Check-out</label>
                <div class="rp-avail-input-wrap" data-rp-open-calendar>
                    <input type="text" class="rp-avail-input" value="{{ old('check_out_date', now()->addDay()->format('m/d/Y')) }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                    <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                </div>
                <input type="hidden" name="check_out_date" value="{{ old('check_out_date', now()->addDay()->toDateString()) }}" data-stay-check-out required>
            </div>
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    Estimated total: <strong data-rp-walkin-total>—</strong>
                    <span class="small text-muted">(room rate × number of nights)</span>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Adults</label>
                <input type="number" min="1" name="adults" class="form-control @error('adults') is-invalid @enderror" value="{{ old('adults', 1) }}" data-rp-guest-adults required>
                @error('adults')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Children</label>
                <input type="number" min="0" name="children" class="form-control" value="{{ old('children', 0) }}" data-rp-guest-children required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Guests</label>
                <input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', (int) old('adults', 1) + (int) old('children', 0)) }}" data-rp-guest-total readonly required>
                <div class="text-danger small d-none" data-rp-capacity-error>Exceeds capacity.</div>
            </div>
            <div class="col-12">
                <label class="form-label">Special requests</label>
                <textarea name="special_requests" class="form-control" rows="2">{{ old('special_requests') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment amount</label>
                <input type="text" inputmode="decimal" name="payment_amount" class="form-control @error('payment_amount') is-invalid @enderror" value="{{ old('payment_amount') }}" autocomplete="off" required>
                @error('payment_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment method</label>
                <select name="payment_method" id="paymentMethod" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>
            <div class="col-md-4" data-rp-pay-ref-wrap>
                <label class="form-label">Reference number</label>
                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
            </div>
        </div>
        <button class="btn btn-rp-primary mt-3">Create Walk-in Booking</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.querySelector('[data-rp-availability-form]');
        const accommodation = form?.querySelector('[data-rp-capacity-select]');
        const checkIn = form?.querySelector('[data-stay-check-in]');
        const checkOut = form?.querySelector('[data-stay-check-out]');
        const total = form?.querySelector('[data-rp-walkin-total]');

        const updateTotal = () => {
            if (!accommodation || !checkIn || !checkOut || !total) return;
            const start = new Date(`${checkIn.value}T00:00:00`);
            const end = new Date(`${checkOut.value}T00:00:00`);
            const nights = Math.round((end - start) / 86400000);
            const rate = Number(accommodation.selectedOptions[0]?.dataset.rate || 0);

            total.textContent = nights > 0 && rate > 0
                ? `₱${(nights * rate).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                : '—';
        };

        accommodation?.addEventListener('change', updateTotal);
        checkIn?.addEventListener('change', updateTotal);
        checkOut?.addEventListener('change', updateTotal);
        updateTotal();
    })();
</script>
@endpush

@include('partials.availability-calendar')
