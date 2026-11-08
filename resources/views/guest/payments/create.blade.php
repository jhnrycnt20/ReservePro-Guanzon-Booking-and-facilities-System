@extends('layouts.public')

@section('title', 'Billing')
@section('body_class', 'rp-page-payment-create')

@section('content')
@php
    $remaining = (float) $booking->remaining_balance;
    $depositAmount = (float) ($suggestedDeposit ?? min($deposit ?? 0, $remaining));
    $depositNotMet = ! $booking->hasMetDepositRequirement();
    $minPayable = $depositNotMet ? $depositAmount : 0.01;
@endphp
<div class="container rp-public-page-top rp-payment-create-page pb-4">
    @if($isDraft)
        <a href="{{ route('guest.bookings.create', [
            'accommodation_id' => $booking->accommodation_id,
            'check_in' => $booking->check_in_date?->format('Y-m-d'),
            'check_out' => $booking->check_out_date?->format('Y-m-d'),
        ]) }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Guest Information</a>
    @else
        <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back</a>
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="rp-flow-card rp-payment-create-card rp-guest-booking-form-card">
                <div class="rp-guest-form-heading rp-payment-form-heading">
                    <div><h1>Billing</h1></div>
                </div>
                <div class="rp-payment-balance-panel">
                    <div class="rp-payment-balance-label">Remaining balance</div>
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

                <form id="rpPaymentForm" method="POST" action="{{ $isDraft ? route('guest.payments.store-draft') : route('guest.payments.store', $booking) }}" data-rp-payment-form data-rp-min-amount="{{ number_format($minPayable, 2, '.', '') }}" data-rp-max-amount="{{ number_format($remaining, 2, '.', '') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 mb-3 rp-payment-field">
                        <label class="form-label" for="paymentAmount">Payment Amount</label>
                        <input
                            type="text"
                            inputmode="decimal"
                            name="amount"
                            id="paymentAmount"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', number_format($depositAmount, 2, '.', '')) }}"
                            autocomplete="off"
                            required
                        >
                        <div class="rp-payment-amount-presets small mt-2">
                            @if($depositNotMet)
                                <button type="button" class="rp-quiet-link rp-payment-deposit-option" data-rp-pay-amount="{{ number_format($depositAmount, 2, '.', '') }}">50% Deposit</button>
                            @endif
                            <button type="button" class="rp-quiet-link" data-rp-pay-amount="{{ number_format($remaining, 2, '.', '') }}">Full Amount</button>
                        </div>
                        @if($depositNotMet)
                            <div class="form-text">Minimum payment: &#8369;{{ number_format($depositAmount, 2) }}</div>
                        @else
                            <div class="form-text">Deposit met. You can pay any amount up to the remaining balance.</div>
                        @endif
                        @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 mb-3 rp-payment-field">
                        <label class="form-label">Payment Method</label>
                        <div class="rp-payment-method-card">
                            <span class="rp-payment-method-icon" aria-hidden="true"><i class="bi bi-phone"></i></span>
                            <span><strong>GCash</strong><small>Secure online payment</small></span>
                            <i class="bi bi-shield-check rp-payment-method-secure" aria-hidden="true"></i>
                        </div>
                        </div>
                    </div>
                </form>

                @if(!$booking->promo_code && ((float) $booking->paid_amount) <= 0)
                    <div class="rp-payment-promo mt-4 pt-4" id="rp-promo-section">
                        <label class="form-label" for="promo_code">Promo Code (Optional)</label>
                        <form method="POST" action="{{ $isDraft ? route('guest.payments.apply-draft-promo') : route('guest.bookings.apply_promo', $booking) }}#rp-promo-section">
                            @csrf
                            <div class="rp-promo-apply">
                                <input type="text" id="promo_code" name="promo_code" class="form-control text-uppercase @error('promo_code') is-invalid @enderror" value="{{ old('promo_code') }}" placeholder="Enter promo code" maxlength="32">
                                <button type="submit" class="rp-btn-check-availability rp-btn-check-availability--inline">Apply</button>
                            </div>
                            @error('promo_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Optional. Enter a code to lower your remaining balance before paying.</div>
                        </form>
                    </div>
                @endif
                <button type="submit" form="rpPaymentForm" class="rp-avail-btn-primary rp-payment-submit-button">Continue to GCash</button>
                <div class="rp-payment-secure-note"><i class="bi bi-lock" aria-hidden="true"></i> You'll be redirected to GCash for secure payment.</div>
                @if(app()->environment(['local', 'testing']))
                    <form method="POST" action="{{ $isDraft ? route('guest.payments.sample-success-draft') : route('guest.payments.sample-success', $booking) }}" class="rp-payment-sample-form">
                        @csrf
                        <input type="hidden" name="amount" value="{{ old('amount', number_format($depositAmount, 2, '.', '')) }}" data-rp-sample-amount>
                        <button type="submit" class="rp-payment-sample-button">Complete sample payment</button>
                        <small>Local demo only · no real payment will be charged</small>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
