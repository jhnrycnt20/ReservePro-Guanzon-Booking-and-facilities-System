@extends('layouts.dashboard')

@section('title', 'Payment Details')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Payment Details')
@section('page_subtitle', ($payment->booking->short_number ?? 'Booking').' · ₱'.number_format($payment->amount, 2))
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<a href="{{ route('front_desk.payments.index') }}" class="rp-back-link mb-3 d-inline-flex"><i class="bi bi-arrow-left"></i> Back to Payments</a>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="h5 mb-0">Payment info</h2>
                <x-status-badge :status="$payment->status" />
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Amount</div>
                    <div class="fs-4 fw-semibold">₱{{ number_format($payment->amount, 2) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Method</div>
                    <div>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Reference number</div>
                    <div class="fw-semibold">{{ $payment->reference_number ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Payment date</div>
                    <div>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Receipt</div>
                    <div>{{ $payment->receipt_number ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Submitted by</div>
                    <div>{{ $payment->processor?->name ?? '—' }}</div>
                </div>
                @if($payment->verified_at)
                    <div class="col-md-6">
                        <div class="text-muted small">Verified / reviewed by</div>
                        <div>{{ $payment->verifier?->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Reviewed at</div>
                        <div>{{ $payment->verified_at?->format('M d, Y g:i A') }}</div>
                    </div>
                @endif
                @if($payment->notes)
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div>{{ $payment->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="rp-card mb-4">
            <h2 class="h5 mb-3">Booking</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Booking ID</div>
                    <div>
                        <a href="{{ route('front_desk.reservations.show', $payment->booking) }}">
                            {{ $payment->booking->short_number ?? $payment->booking->booking_number }}
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Guest</div>
                    <div>{{ $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Room</div>
                    <div>{{ $payment->booking->accommodation->name ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Contact</div>
                    <div>{{ $payment->booking->contact_number }} · {{ $payment->booking->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Stay</div>
                    <div>{{ $payment->booking->check_in_date?->format('M d, Y') }} → {{ $payment->booking->check_out_date?->format('M d, Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Booking total / paid / remaining</div>
                    <div>
                        ₱{{ number_format($payment->booking->total_amount, 2) }} /
                        ₱{{ number_format($payment->booking->paid_amount, 2) }} /
                        ₱{{ number_format($payment->booking->remaining_balance, 2) }}
                    </div>
                </div>
            </div>
        </div>

        @if(($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status) === 'pending')
            <div class="rp-card">
                <h2 class="h5 mb-3">Actions</h2>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('front_desk.payments.verify', $payment) }}">
                        @csrf
                        <button class="btn btn-rp-primary" data-rp-confirm-click="Verify this payment?">Verify Payment</button>
                    </form>
                    <form method="POST" action="{{ route('front_desk.payments.reject', $payment) }}" class="flex-grow-1">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="notes" class="form-control" placeholder="Reject reason (optional)">
                            <button class="btn btn-outline-danger" data-rp-confirm-click="Reject this payment?">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="rp-card">
                <button type="button" class="btn btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpPaymentReceiptModal">
                    View Receipt
                </button>
            </div>
        @endif
    </div>

    <div class="col-lg-6">
        <div class="rp-card">
            <h2 class="h5 mb-3">Payment screenshot / proof</h2>
            @if($payment->proof_url)
                <button
                    type="button"
                    class="rp-payment-proof-link border-0 bg-transparent p-0 w-100 text-start"
                    data-bs-toggle="modal"
                    data-bs-target="#rpPaymentProofModal"
                    aria-label="View payment proof"
                >
                    <img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-img">
                </button>
            @else
                <div class="alert alert-warning mb-0">No screenshot was uploaded for this payment.</div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="rpPaymentReceiptModal" tabindex="-1" aria-labelledby="rpPaymentReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpPaymentReceiptModalLabel">Payment Receipt</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-receipt-card" id="rpPaymentReceiptPrint">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="rp-receipt-brand">{{ $resortSettings['resort_name'] ?? 'ReservePro' }}</div>
                            <div class="rp-receipt-meta">Payment Receipt</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-dark no-print" data-rp-print-receipt="#rpPaymentReceiptPrint">Print</button>
                    </div>
                    <hr>
                    <div class="rp-receipt-row"><span>Receipt #</span><strong>{{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</strong></div>
                    <div class="rp-receipt-row"><span>Booking</span><strong>{{ $payment->booking->short_number ?? $payment->booking->booking_number ?? '—' }}</strong></div>
                    <div class="rp-receipt-row"><span>Guest</span><strong>{{ $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—' }}</strong></div>
                    <div class="rp-receipt-row"><span>Amount</span><strong>₱{{ number_format($payment->amount, 2) }}</strong></div>
                    <div class="rp-receipt-row">
                        <span>Method</span>
                        <strong>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</strong>
                    </div>
                    <div class="rp-receipt-row"><span>Reference</span><strong>{{ $payment->reference_number ?? '—' }}</strong></div>
                    <div class="rp-receipt-row"><span>Payment date</span><strong>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</strong></div>
                    <div class="rp-receipt-row">
                        <span>Status</span>
                        <strong>{{ str_replace('_', ' ', ucfirst($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status)) }}</strong>
                    </div>
                    @if($payment->verified_at)
                        <div class="rp-receipt-row"><span>Verified at</span><strong>{{ $payment->verified_at->format('M d, Y g:i A') }}</strong></div>
                    @endif
                    <hr>
                    <p class="rp-receipt-meta mb-0 text-center">Thank you for staying with us.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($payment->proof_url)
<div class="modal fade" id="rpPaymentProofModal" tabindex="-1" aria-labelledby="rpPaymentProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpPaymentProofModalLabel">Payment screenshot</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-modal-img">
                <div class="small text-muted mt-3 mb-0">
                    Ref: {{ $payment->reference_number ?: '—' }} ·
                    ₱{{ number_format($payment->amount, 2) }} ·
                    {{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}
                </div>
            </div>
            @if(($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status) === 'pending')
                <div class="modal-footer border-0 pt-0">
                    <form method="POST" action="{{ route('front_desk.payments.verify', $payment) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-rp-primary" data-rp-confirm-click="Verify this payment?">Verify</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection
