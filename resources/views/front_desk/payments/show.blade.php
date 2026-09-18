@extends('layouts.dashboard')

@section('title', 'Payment Details')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Payment Details')
@section('page_subtitle', ($payment->booking->short_number ?? 'Booking').' · ₱'.number_format($payment->amount, 2))
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@php
    $bookingTotal = (float) ($payment->booking?->total_amount ?? 0);
    $bookingPaid = (float) ($payment->booking?->paid_amount ?? 0);
    $remainingBalance = (float) ($payment->booking?->remaining_balance ?? max(0, $bookingTotal - $bookingPaid));
    $isPartial = $remainingBalance > 0.009;
    $paymentShare = $bookingTotal > 0 ? round(((float) $payment->amount / $bookingTotal) * 100) : 0;
    $paymentDisplayStatus = $bookingTotal > 0 && $bookingPaid + 0.009 >= $bookingTotal
        ? 'fully_paid'
        : ($bookingPaid > 0 ? 'partially_paid' : 'unpaid');
    $paymentDisplayLabel = match ($paymentDisplayStatus) {
        'fully_paid' => 'Fully Paid',
        'partially_paid' => 'Partially Paid',
        default => 'Unpaid',
    };
    $methodLabel = str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method));
    $guestName = $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—';
    $bookingCode = $payment->booking->short_number ?? $payment->booking->booking_number ?? '—';
@endphp

@section('content')
<a href="{{ route('front_desk.reservations.index') }}#payments" class="rp-back-link mb-3 d-inline-flex"><i class="bi bi-arrow-left"></i> Back to Reservations</a>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3"><h2 class="h5 mb-0">Payment info</h2><x-status-badge :status="$paymentDisplayStatus" :label="$paymentDisplayLabel" /></div>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Amount</div><div class="fs-4 fw-semibold">₱{{ number_format($payment->amount, 2) }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Method</div><div>{{ $methodLabel }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Reference</div><div>{{ $payment->reference_number ?: '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Date</div><div>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Receipt</div><div>{{ $payment->receipt_number ?: '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Submitted by</div><div>{{ $payment->processor?->name ?? '—' }}</div></div>
                @if($payment->verified_at)
                    <div class="col-md-6"><div class="text-muted small">Verified / reviewed by</div><div>{{ $payment->verifier?->name ?? '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Reviewed at</div><div>{{ $payment->verified_at?->format('M d, Y g:i A') }}</div></div>
                @endif
                @if($payment->notes)<div class="col-12"><div class="text-muted small">Notes</div><div>{{ $payment->notes }}</div></div>@endif
            </div>
        </div>

        <div class="rp-card mb-4">
            <h2 class="h5 mb-3">Booking</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Booking ID</div><div><a href="{{ route('front_desk.reservations.show', $payment->booking) }}">{{ $bookingCode }}</a></div></div>
                <div class="col-md-6"><div class="text-muted small">Guest</div><div>{{ $guestName }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Room</div><div>{{ $payment->booking->accommodation->name ?? '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Contact</div><div>{{ $payment->booking->contact_number }} · {{ $payment->booking->email }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Dates</div><div>{{ $payment->booking->check_in_date?->format('M d, Y') }} → {{ $payment->booking->check_out_date?->format('M d, Y') }}</div></div>
                <div class="col-md-6">
                    <div class="text-muted small">Booking</div>
                    <div><span class="text-muted">Total</span> ₱{{ number_format($bookingTotal, 2) }}</div>
                    <div><span class="text-muted">Paid</span> ₱{{ number_format($bookingPaid, 2) }}</div>
                    <div><span class="text-muted">Remaining balance</span> ₱{{ number_format($remainingBalance, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="rp-card">
            <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#rpPaymentReceiptModal">
                <i class="bi bi-receipt me-1" aria-hidden="true"></i>
                View Receipt
            </button>
        </div>
    </div>

    <div class="col-lg-6"><div class="rp-card"><h2 class="h5 mb-3">Payment screenshot / proof</h2>
        @if($payment->proof_url)
            <button type="button" class="rp-payment-proof-link border-0 bg-transparent p-0 w-100 text-start" data-bs-toggle="modal" data-bs-target="#rpPaymentProofModal" aria-label="View payment proof"><img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-img"></button>
        @else
            <div class="alert alert-warning mb-0">No screenshot was uploaded for this payment.</div>
        @endif
    </div></div>
</div>

<div class="modal fade" id="rpPaymentReceiptModal" tabindex="-1" aria-labelledby="rpPaymentReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-receipt-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpPaymentReceiptModalLabel">Payment receipt</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-receipt-card" id="rpPaymentReceiptPrint">
                    <div class="rp-receipt-brand">{{ $resortSettings['resort_name'] ?? 'Guanzon Beach' }}</div>
                    <div class="rp-receipt-meta">Payment receipt</div>
                    <hr class="rp-receipt-divider">
                    <div class="rp-receipt-amount">₱{{ number_format($payment->amount, 2) }}</div>
                    <div class="rp-receipt-meta text-center mb-2">Verified payment</div>

                    @if($isPartial)
                        <div class="rp-receipt-balance-note">
                            <div class="rp-receipt-balance-note-title">
                                {{ $paymentShare >= 45 && $paymentShare <= 55
                                    ? 'This is a 50% deposit payment. A remaining balance is still due before or on check-in.'
                                    : 'This is a partial payment. A remaining balance is still due before or on check-in.' }}
                            </div>
                            <div class="rp-receipt-row mb-0"><span>Booking total</span><strong>₱{{ number_format($bookingTotal, 2) }}</strong></div>
                            <div class="rp-receipt-row mb-0"><span>Total paid</span><strong>₱{{ number_format($bookingPaid, 2) }}</strong></div>
                            <div class="rp-receipt-row mb-0"><span>Balance left</span><strong class="rp-receipt-balance-left">₱{{ number_format(max(0, $remainingBalance), 2) }}</strong></div>
                        </div>
                    @endif

                    <div class="rp-receipt-row"><span>Receipt</span><strong>{{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</strong></div>
                    <div class="rp-receipt-row"><span>Booking</span><strong>{{ $bookingCode }}</strong></div>
                    <div class="rp-receipt-row"><span>Guest</span><strong>{{ $guestName }}</strong></div>
                    @if($payment->booking?->accommodation)
                        <div class="rp-receipt-row"><span>Room</span><strong>{{ $payment->booking->accommodation->name }}</strong></div>
                    @endif
                    <div class="rp-receipt-row"><span>Method</span><strong>{{ $methodLabel }}</strong></div>
                    <div class="rp-receipt-row"><span>Reference</span><strong>{{ $payment->reference_number ?? '—' }}</strong></div>
                    <div class="rp-receipt-row"><span>Paid on</span><strong>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</strong></div>
                    @if($payment->verified_at)
                        <div class="rp-receipt-row"><span>Verified</span><strong>{{ $payment->verified_at->format('M d, Y g:i A') }}</strong></div>
                    @endif
                    <hr class="rp-receipt-divider">
                    <p class="rp-receipt-thanks">Thank you for staying with us.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-rp-primary" data-rp-print-receipt="#rpPaymentReceiptPrint">
                    <i class="bi bi-download me-1" aria-hidden="true"></i>
                    Download
                </button>
            </div>
        </div>
    </div>
</div>

@if($payment->proof_url)
<div class="modal fade" id="rpPaymentProofModal" tabindex="-1" aria-labelledby="rpPaymentProofModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header border-0 pb-0"><h2 class="modal-title h5" id="rpPaymentProofModalLabel">Payment screenshot</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body pt-3"><img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-modal-img"><div class="small text-muted mt-3 mb-0">Ref: {{ $payment->reference_number ?: '—' }} · ₱{{ number_format($payment->amount, 2) }} · {{ $methodLabel }}</div></div>
</div></div></div>
@endif
@endsection
