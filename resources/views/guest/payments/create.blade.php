@extends('layouts.public')

@section('title', 'Make Payment')

@section('content')
@php
    $remaining = (float) $booking->remaining_balance;
    $depositAmount = (float) ($suggestedDeposit ?? min($deposit ?? 0, $remaining));
    $depositNotMet = ! $booking->hasMetDepositRequirement();
    $minPayable = $depositNotMet ? $depositAmount : 0.01;
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

                <form method="POST" action="{{ route('guest.payments.store', $booking) }}" data-rp-payment-form data-rp-min-amount="{{ number_format($minPayable, 2, '.', '') }}" data-rp-max-amount="{{ number_format($remaining, 2, '.', '') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="paymentAmount">Amount</label>
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
                        <div class="small mt-1">
                            @if($depositNotMet)
                                <button type="button" class="rp-quiet-link" data-rp-pay-amount="{{ number_format($depositAmount, 2, '.', '') }}">Use 50% deposit (₱{{ number_format($depositAmount, 2) }})</button>
                                &middot;
                            @endif
                            <button type="button" class="rp-quiet-link" data-rp-pay-amount="{{ number_format($remaining, 2, '.', '') }}">Use full amount</button>
                        </div>
                        @if($depositNotMet)
                            <div class="form-text">You can type any amount, but it must be at least ₱{{ number_format($depositAmount, 2) }} (50% deposit).</div>
                        @else
                            <div class="form-text">Deposit met. You can pay any amount up to the remaining balance.</div>
                        @endif
                        @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode of Payment</label>
                        <input type="text" class="form-control" value="GCash" disabled readonly>
                        <div class="form-text">You'll be redirected to GCash to complete your payment securely.</div>
                    </div>
                    <button class="rp-avail-btn-primary">Pay with GCash</button>
                </form>

                @if(!$booking->promo_code && ((float) $booking->paid_amount) <= 0)
                    <div class="mt-3 pt-3 border-top" id="rp-promo-section">
                        <label class="form-label" for="promo_code">Promo code</label>
                        <form method="POST" action="{{ route('guest.bookings.apply_promo', $booking) }}#rp-promo-section">
                            @csrf
                            <div class="rp-promo-apply">
                                <input type="text" id="promo_code" name="promo_code" class="form-control text-uppercase @error('promo_code') is-invalid @enderror" value="{{ old('promo_code') }}" placeholder="Enter code" maxlength="32">
                                <button type="submit" class="rp-btn-check-availability rp-btn-check-availability--inline">Apply</button>
                            </div>
                            @error('promo_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Optional. Enter a code to lower your remaining balance before paying.</div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
