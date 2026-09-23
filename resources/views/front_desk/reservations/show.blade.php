@extends('layouts.dashboard')

@section('title', 'Reservation Details')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Reservation '.$booking->short_number)
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status;
    $isReserved = in_array($bookingStatus, ['pending', 'approved'], true) && ! $booking->hasMetDepositRequirement();
    $isFullyPaid = $booking->isFullyPaid();
    $canManualCheckIn = $bookingStatus === 'approved' && $isFullyPaid && ! $booking->checkIn;
    $checkInWindowOpen = $booking->checkInWindowOpen();
    $checkInTime = \Carbon\Carbon::createFromFormat('H:i', (string) config('resort.check_in_time', '14:00'))->format('g:i A');
    $payment = $booking->payments->sortByDesc('payment_date')->first();
    $bookingTotal = (float) $booking->total_amount;
    $bookingPaid = (float) $booking->paid_amount;
    $remainingBalance = (float) $booking->remaining_balance;
    $isPartial = $remainingBalance > 0.009;
    $paymentShare = ($payment && $bookingTotal > 0)
        ? round(((float) $payment->amount / $bookingTotal) * 100)
        : 0;
    $paymentDisplayStatus = $bookingTotal > 0 && $bookingPaid + 0.009 >= $bookingTotal
        ? 'fully_paid'
        : ($bookingPaid > 0 ? 'partially_paid' : 'unpaid');
    $paymentDisplayLabel = match ($paymentDisplayStatus) {
        'fully_paid' => 'Fully Paid',
        'partially_paid' => 'Partially Paid',
        default => 'Unpaid',
    };
    $methodLabel = $payment
        ? str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method))
        : '—';
    $guestName = $booking->guest_name ?? $booking->guest?->user?->name ?? '—';
    $bookingCode = $booking->short_number ?? $booking->booking_number;
@endphp

@if(request()->boolean('created'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Thank you for booking with us!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="h5 mb-0">Payment info</h2>
                <div class="d-flex align-items-center gap-2">
                    @if($payment)
                        <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpReservationReceiptModal">
                            Show Receipt
                        </button>
                        @if($payment->proof_url)
                            <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpReservationProofModal">
                                Show Payment
                            </button>
                        @endif
                    @endif
                </div>
            </div>
            @if($payment)
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Amount</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 fw-semibold">₱{{ number_format($payment->amount, 2) }}</span>
                            <x-status-badge :status="$paymentDisplayStatus" :label="$paymentDisplayLabel" plain />
                        </div>
                    </div>
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
            @else
                <div class="text-muted">No payment recorded yet.</div>
                @if($isReserved)
                    <div class="small text-muted mt-2">₱{{ number_format($booking->depositRequiredAmount(), 2) }} deposit needed to confirm.</div>
                @endif
            @endif
            <a href="{{ route('front_desk.reservations.index') }}" class="btn btn-rp-soft mt-3">Back</a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="rp-card">
            @if($canManualCheckIn)
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="h5 mb-0">Ready for check-in</h2>
                        @if($checkInWindowOpen)
                            <div class="small text-muted mb-0">Fully paid. You can check this guest in now.</div>
                        @else
                            <div class="small text-muted mb-0">
                                Fully paid. Check-in opens on {{ $booking->check_in_date->format('M d, Y') }} at {{ $checkInTime }}.
                            </div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($checkInWindowOpen)
                            <form method="POST" action="{{ route('front_desk.checkins.store', $booking) }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-rp-primary" data-rp-confirm-click="Check in this guest now?">
                                    <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
                                    Check in
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-sm btn-rp-soft" disabled>Check in</button>
                        @endif
                    </div>
                </div>
                @error('check_in_date')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
                @error('payment')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
                @error('status')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
            @endif
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted small">Booking ID</div><div>{{ $bookingCode }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Guest</div><div>{{ $guestName }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Room</div><div>{{ $booking->accommodation?->name ?? '—' }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Contact</div><div>{{ $booking->contact_number }} · {{ $booking->email }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Dates</div><div>{{ $booking->check_in_date?->format('M d, Y') }} → {{ $booking->check_out_date?->format('M d, Y') }}</div></div>
                <div class="col-md-6">
                    <div><span class="text-muted">Total</span> ₱{{ number_format($bookingTotal, 2) }}</div>
                    <div><span class="text-muted">Paid</span> ₱{{ number_format($bookingPaid, 2) }}</div>
                    <div><span class="text-muted">Remaining balance</span> ₱{{ number_format($remainingBalance, 2) }}</div>
                    @if($booking->hasMetDepositRequirement() && ! $booking->isFullyPaid())
                        <div class="small text-muted mt-1">Due before check-in at 2:00 PM.</div>
                    @endif
                </div>
                <div class="col-12 mt-n2"><div class="text-muted small">Guests</div><div>{{ $booking->number_of_guests }} / {{ $booking->accommodation?->capacity ?? '—' }} max</div></div>
                @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                    <div class="col-md-6">
                        <div class="text-muted small">Promo</div>
                        <div>
                            <code>{{ $booking->promo_code ?: ($booking->promo?->code ?? '—') }}</code>
                            @if($booking->discount_percent)
                                · {{ rtrim(rtrim(number_format((float) $booking->discount_percent, 2), '0'), '.') }}% off
                            @endif
                        </div>
                        <div class="text-success small">Saved ₱{{ number_format((float) $booking->discount_amount, 2) }}</div>
                    </div>
                @endif
                @if($booking->special_requests)
                    <div class="col-12"><div class="text-muted small">Special requests</div><div>{{ $booking->special_requests }}</div></div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($payment)
<div class="modal fade" id="rpReservationReceiptModal" tabindex="-1" aria-labelledby="rpReservationReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-receipt-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpReservationReceiptModalLabel">Payment receipt</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-receipt-card" id="rpReservationReceiptPrint">
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
                    <div class="rp-receipt-row"><span>Room</span><strong>{{ $booking->accommodation?->name ?? '—' }}</strong></div>
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
                <button type="button" class="btn btn-rp-primary" data-rp-print-receipt="#rpReservationReceiptPrint">
                    <i class="bi bi-download me-1" aria-hidden="true"></i>
                    Download
                </button>
            </div>
        </div>
    </div>
</div>

@if($payment->proof_url)
<div class="modal fade" id="rpReservationProofModal" tabindex="-1" aria-labelledby="rpReservationProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpReservationProofModalLabel">Payment screenshot</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <img src="{{ $payment->proof_url }}" alt="Payment proof screenshot" class="rp-payment-proof-modal-img">
                <div class="small text-muted mt-3 mb-0">Ref: {{ $payment->reference_number ?: '—' }} · ₱{{ number_format($payment->amount, 2) }} · {{ $methodLabel }}</div>
            </div>
        </div>
    </div>
</div>
@endif
@endif
@endsection
