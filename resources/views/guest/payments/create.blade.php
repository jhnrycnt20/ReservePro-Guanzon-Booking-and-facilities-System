@extends('layouts.public')

@section('title', 'Make Payment')

@section('content')
@php
    $remaining = (float) $booking->remaining_balance;
    $depositAmount = (float) ($suggestedDeposit ?? min($deposit ?? 0, $remaining));
@endphp
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Make Payment</h1>
    </div>

    <div class="row g-4">
        <div class="col-lg-7 mx-auto">
            <div class="rp-flow-card">
                <div class="mb-3">
                    <div class="text-muted small">Remaining balance</div>
                    <div class="rp-remaining-balance-amount">₱{{ number_format($remaining, 2) }}</div>
                    @if($booking->promo_code)
                        <div class="small text-success mt-1">
                            Promo <code>{{ $booking->promo_code }}</code> applied
                            @if($booking->discount_amount)
                                &middot; saved ₱{{ number_format((float) $booking->discount_amount, 2) }}
                            @endif
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('guest.payments.store', $booking) }}" enctype="multipart/form-data" data-rp-payment-form>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="text" inputmode="decimal" name="amount" id="paymentAmount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', number_format($depositAmount, 2, '.', '')) }}" autocomplete="off" required>
                        <div class="small mt-1">
                            <button type="button" class="rp-quiet-link" data-rp-pay-amount="{{ number_format($depositAmount, 2, '.', '') }}">Use 50% deposit (₱{{ number_format($depositAmount, 2) }})</button>
                            &middot;
                            <button type="button" class="rp-quiet-link" data-rp-pay-amount="{{ number_format($remaining, 2, '.', '') }}">Use full amount</button>
                        </div>
                        @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode of Payment</label>
                        <select name="payment_method" id="paymentMethod" class="form-select @error('payment_method') is-invalid @enderror" required>
                            @foreach(['gcash' => 'GCash', 'cash' => 'Cash (at front desk)'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method', 'gcash') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3" data-rp-pay-qr-wrap>
                        <button type="button" class="rp-quiet-link" data-bs-toggle="modal" data-bs-target="#rpPayQrModal">Show QR Code</button>
                    </div>
                    <div class="mb-3" data-rp-pay-ref-wrap>
                        <label class="form-label">Reference number</label>
                        <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number') }}" placeholder="GCash reference">
                        @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3" data-rp-pay-proof-wrap>
                        <label class="form-label">Receipt Screenshot</label>
                        <input type="file" name="proof" accept="image/*" class="form-control @error('proof') is-invalid @enderror">
                        <div class="form-text">JPG or PNG, max 5MB.</div>
                        @error('proof')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment date</label>
                        <input type="datetime-local" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <button class="rp-avail-btn-primary">Submit Payment</button>
                </form>

                @if(!$booking->promo_code && ((float) $booking->paid_amount) <= 0)
                    <div class="mt-3 pt-3 border-top">
                        <button type="button" class="rp-quiet-link" data-bs-toggle="collapse" data-bs-target="#rpPromoCollapse">Have a promo code?</button>
                        <div class="collapse mt-2" id="rpPromoCollapse">
                            <form method="POST" action="{{ route('guest.bookings.apply_promo', $booking) }}">
                                @csrf
                                <div class="rp-promo-apply">
                                    <input type="text" name="promo_code" class="form-control text-uppercase @error('promo_code') is-invalid @enderror" placeholder="Enter code" maxlength="32" required>
                                    <button type="submit" class="rp-btn-check-availability rp-btn-check-availability--inline">Apply</button>
                                </div>
                                @error('promo_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rpPayQrModal" tabindex="-1" aria-labelledby="rpPayQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rp-pay-qr-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h6" id="rpPayQrModalLabel">Scan to Pay via GCash</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <div class="rp-pay-qr-frame mb-2">
                    <button
                        type="button"
                        class="rp-gcash-qr-wrap rp-gcash-qr-wrap--compact border-0 bg-transparent p-0"
                        data-rp-open-gcash
                        data-gcash-number="{{ $resortSettings['gcash_number'] ?? '09505584607' }}"
                        data-gcash-amount="{{ number_format($depositAmount, 2, '.', '') }}"
                        aria-label="Open GCash to pay"
                    >
                        <img src="{{ asset('images/gcash-qr.jpg') }}" alt="GCash QR code for Guanzon Beach" class="rp-gcash-qr">
                    </button>
                    <button type="button" class="rp-view-full-image-btn" data-bs-toggle="modal" data-bs-target="#rpPayQrZoomModal" aria-label="View full QR code">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
                <div class="small text-muted">
                    {{ $resortSettings['gcash_number'] ?? '09505584607' }}
                    &middot; {{ $resortSettings['gcash_name'] ?? ($resortSettings['resort_name'] ?? 'Guanzon Beach') }}
                </div>
                <div class="small text-muted mt-1">
                    {{ $resortSettings['bank_name'] ?? 'BDO' }}
                    &middot; {{ $resortSettings['bank_account_name'] ?? ($resortSettings['resort_name'] ?? 'Guanzon Beach') }}
                    &middot; {{ $resortSettings['bank_account_number'] ?? '0000-0000-0000' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rpPayQrZoomModal" tabindex="-1" aria-labelledby="rpPayQrZoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-pay-qr-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h6" id="rpPayQrZoomModalLabel">GCash QR Code</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <img src="{{ asset('images/gcash-qr.jpg') }}" alt="GCash QR code for Guanzon Beach" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection
