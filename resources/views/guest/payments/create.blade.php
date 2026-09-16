@extends('layouts.dashboard')

@section('title', 'Make Payment')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Make Payment')
@section('page_subtitle', 'Booking '.$booking->short_number)
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
@php
    $remaining = (float) $booking->remaining_balance;
    $depositAmount = (float) ($suggestedDeposit ?? min($deposit ?? 0, $remaining));
    $paymongoEnabled = $paymongoEnabled ?? false;
@endphp
<div class="row g-4">
    <div class="col-lg-5">
        <div class="rp-card h-100">
            <h2 class="h5 mb-3">How to pay</h2>
            @if($paymongoEnabled)
                <p class="text-muted small mb-3">
                    Pay with <strong>GCash online</strong> — the exact amount is sent to PayMongo and confirmed automatically.
                    You can still use manual bank transfer below if needed.
                </p>
            @else
                <p class="text-muted small mb-3">
                    Pay a <strong>50% deposit</strong> (or the full balance) via GCash or bank transfer, then upload your proof.
                    Front desk will verify before check-in.
                </p>
            @endif

            @unless($paymongoEnabled)
                <div class="rp-pay-detail mb-3">
                    <div class="rp-pay-detail-label">GCash (manual)</div>
                    <div class="rp-pay-detail-value">{{ $resortSettings['gcash_number'] ?? '09505584607' }}</div>
                    <div class="small text-muted mb-3">{{ $resortSettings['gcash_name'] ?? ($resortSettings['resort_name'] ?? 'Guanzon Beach') }}</div>
                    <button
                        type="button"
                        class="rp-gcash-qr-wrap mb-3 border-0 bg-transparent p-0 w-100"
                        data-rp-open-gcash
                        data-gcash-number="{{ $resortSettings['gcash_number'] ?? '09505584607' }}"
                        data-gcash-amount="{{ number_format($depositAmount, 2, '.', '') }}"
                        aria-label="Open GCash to pay"
                    >
                        <img src="{{ asset('images/gcash-qr.jpg') }}" alt="GCash QR code for Guanzon Beach" class="rp-gcash-qr">
                        <div class="small text-muted mt-2">Tap QR or button to open GCash</div>
                    </button>
                    <button
                        type="button"
                        class="btn btn-rp-primary w-100"
                        data-rp-open-gcash
                        data-gcash-number="{{ $resortSettings['gcash_number'] ?? '09505584607' }}"
                        data-gcash-amount="{{ number_format($depositAmount, 2, '.', '') }}"
                    >
                        Open GCash to Pay
                    </button>
                    <div class="form-text mt-2">Opens the GCash app and copies <strong>{{ $resortSettings['gcash_number'] ?? '09505584607' }}</strong>. Paste it in Send Money, then upload your proof on the right.</div>
                </div>
            @endunless

            <div class="rp-pay-detail mb-3">
                <div class="rp-pay-detail-label">Bank transfer</div>
                <div class="rp-pay-detail-value">{{ $resortSettings['bank_name'] ?? 'BDO' }}</div>
                <div class="small">Account name: {{ $resortSettings['bank_account_name'] ?? ($resortSettings['resort_name'] ?? 'Guanzon Beach') }}</div>
                <div class="small">Account no.: {{ $resortSettings['bank_account_number'] ?? '0000-0000-0000' }}</div>
            </div>
            <div class="alert alert-success mb-0 small">
                Required deposit: <strong>₱{{ number_format($deposit ?? $depositAmount, 2) }}</strong>
                ({{ \App\Services\PaymentService::DEPOSIT_PERCENT }}% of ₱{{ number_format($booking->total_amount, 2) }})
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="rp-card">
            <div class="mb-3">
                <div class="text-muted small">Remaining balance</div>
                <div class="fs-3" style="font-family: var(--rp-display);">₱{{ number_format($remaining, 2) }}</div>
            </div>

            @if($paymongoEnabled)
                <div class="mb-4 pb-4 border-bottom" data-rp-gcash-gateway>
                    <h3 class="h6 mb-2">Pay with GCash</h3>
                    <p class="small text-muted mb-3">You will be redirected to GCash to pay the exact amount below.</p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-gcash-pay-amount="{{ number_format($depositAmount, 2, '.', '') }}">
                            50% Deposit (₱{{ number_format($depositAmount, 2) }})
                        </button>
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-gcash-pay-amount="{{ number_format($remaining, 2, '.', '') }}">
                            Pay Full (₱{{ number_format($remaining, 2) }})
                        </button>
                    </div>
                    <form method="POST" action="{{ route('guest.payments.gcash', $booking) }}" class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-end">
                        @csrf
                        <div class="flex-grow-1">
                            <label class="form-label" for="gcashGatewayAmount">Amount (₱)</label>
                            <input type="number" step="0.01" min="1" max="{{ $remaining }}" name="amount" id="gcashGatewayAmount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', number_format($depositAmount, 2, '.', '')) }}" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('payment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-rp-primary px-4">Pay with GCash</button>
                    </form>
                </div>
                <h3 class="h6 mb-3">Manual payment (proof upload)</h3>
            @endif

            <form method="POST" action="{{ route('guest.payments.store', $booking) }}" enctype="multipart/form-data" data-rp-payment-form>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-pay-amount="{{ number_format($depositAmount, 2, '.', '') }}">
                            50% Deposit (₱{{ number_format($depositAmount, 2) }})
                        </button>
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-pay-amount="{{ number_format($remaining, 2, '.', '') }}">
                            Pay Full (₱{{ number_format($remaining, 2) }})
                        </button>
                    </div>
                    <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="amount" id="paymentAmount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', number_format($depositAmount, 2, '.', '')) }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment method</label>
                    <select name="payment_method" id="paymentMethod" class="form-select @error('payment_method') is-invalid @enderror" required>
                        @foreach(['gcash' => 'GCash', 'bank_transfer' => 'Bank Transfer', 'cash' => 'Cash (at front desk)', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', $paymongoEnabled ? 'bank_transfer' : 'gcash') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3" data-rp-pay-ref-wrap>
                    <label class="form-label">Reference number</label>
                    <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number') }}" placeholder="GCash / bank reference">
                    @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3" data-rp-pay-proof-wrap>
                    <label class="form-label">Payment proof (screenshot)</label>
                    <input type="file" name="proof" accept="image/*" class="form-control @error('proof') is-invalid @enderror">
                    <div class="form-text">JPG or PNG, max 5MB. Required for GCash and bank transfer.</div>
                    @error('proof')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment date</label>
                    <input type="datetime-local" name="payment_date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
                <button class="btn btn-rp-primary w-100">{{ $paymongoEnabled ? 'Submit Manual Payment' : 'Submit Payment' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
