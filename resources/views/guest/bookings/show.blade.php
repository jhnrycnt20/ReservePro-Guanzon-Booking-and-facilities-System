@extends('layouts.public')

@section('title', 'Reservation '.$booking->booking_number)

@section('content')
<div class="container rp-public-page-top pb-4">
<a href="{{ route('guest.bookings.index') }}" class="rp-back-link" data-rp-history-back><i class="bi bi-arrow-left"></i> Back</a>

<div class="rp-page-intro">
    <h1 class="rp-page-intro-title">Reservation {{ $booking->short_number }}</h1>
</div>

@include('partials.booking-tracker', ['activeStep' => 'confirmation'])

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-cottage-card rp-cottage-card--static rp-booking-confirm-card mb-4">
            <div class="rp-cottage-media">
                <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}">
            </div>
            <div class="rp-cottage-card-body">
                <div class="rp-cottage-title">{{ $booking->accommodation->name }}</div>
                <div class="rp-booking-list-dates mb-2">
                    <div class="rp-booking-list-date-block">
                        <span class="rp-booking-list-date-label">Check-in</span>
                        <span class="rp-booking-list-date-value">{{ $booking->check_in_date->format('M d, Y') }}</span>
                    </div>
                    <div class="rp-booking-list-date-block">
                        <span class="rp-booking-list-date-label">Check-out</span>
                        <span class="rp-booking-list-date-value">{{ $booking->check_out_date->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="rp-cottage-row">
                    <span>Guests</span>
                    <span>{{ $booking->number_of_guests }} ({{ $booking->adults }} adults, {{ $booking->children }} children)</span>
                </div>
                <div class="rp-cottage-row">
                    <span>Total</span>
                    <span>₱{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                    <div class="rp-cottage-row">
                        <span>Promo</span>
                        <span>
                            <code>{{ $booking->promo_code }}</code>
                            @if($booking->discount_percent)
                                ({{ rtrim(rtrim(number_format((float) $booking->discount_percent, 2), '0'), '.') }}% off)
                            @endif
                        </span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>You saved</span>
                        <span>₱{{ number_format((float) $booking->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="rp-cottage-row">
                    <span>Remaining balance</span>
                    <span>₱{{ number_format($booking->remaining_balance, 2) }}</span>
                </div>
                @php
                    $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
                    $isFullyPaid = ((float) $booking->remaining_balance) <= 0.009;
                    $checkInTime = \Carbon\Carbon::createFromFormat('H:i', (string) config('resort.check_in_time', '14:00'))->format('g:i A');
                @endphp
                @if($isFullyPaid && $bookingStatus === 'approved')
                    <div class="rp-checkin-ready-note mt-3">
                        Fully paid. You can now check in on {{ $booking->check_in_date->format('M d, Y') }} at {{ $checkInTime }}.
                    </div>
                @elseif($isFullyPaid && $bookingStatus === 'checked_in')
                    <div class="rp-checkin-ready-note mt-3">
                        You are checked in. Enjoy your stay.
                    </div>
                @endif
            </div>
        </div>

        @if($booking->special_requests || $booking->rejection_reason)
            <div class="rp-flow-card mb-4">
                @if($booking->special_requests)
                    <h3 class="h6">Special requests</h3>
                    <p class="mb-0">{{ $booking->special_requests }}</p>
                @endif
                @if($booking->rejection_reason)
                    <div class="alert alert-danger {{ $booking->special_requests ? 'mt-3' : '' }} mb-0">Rejected: {{ $booking->rejection_reason }}</div>
                @endif
            </div>
        @endif

    </div>
    <div class="col-lg-4">
        <div class="rp-flow-card mb-3">
            <div class="rp-booking-actions">
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in']) && $booking->remaining_balance > 0)
                <a href="{{ route('guest.payments.create', $booking) }}" class="rp-avail-btn-primary">Proceed to Payment</a>
            @endif
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['pending', 'approved']))
                <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}">
                    @csrf
                    <button type="submit" class="rp-avail-btn-secondary rp-avail-btn-secondary--danger" data-rp-confirm-click="Cancel this reservation?">Cancel Reservation</button>
                </form>
            @endif
            @if(in_array(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status), ['approved', 'checked_in', 'checked_out']))
                <a href="{{ route('guest.incidents.create', ['booking_id' => $booking->id]) }}" class="rp-avail-btn-secondary rp-avail-btn-secondary--danger">Report Issue</a>
            @endif
            @if(($booking->status instanceof \BackedEnum ? $booking->status->value : $booking->status) === 'checked_out' && !$booking->feedback)
                <a href="{{ route('guest.feedback.create_booking', $booking) }}" class="rp-avail-btn-secondary">Leave Feedback</a>
            @endif
            </div>
        </div>

        <div class="rp-flow-card">
            <h3 class="h6">Payments</h3>
            @forelse($booking->payments as $payment)
                @php
                    $paymentStatus = $payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status;
                    $canDownloadReceipt = $paymentStatus === 'verified';
                    $methodLabel = str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method));
                @endphp
                <div class="rp-payment-mini">
                    <div class="rp-payment-mini-top">
                        <span class="rp-payment-mini-amount">₱{{ number_format($payment->amount, 2) }}</span>
                        <x-status-badge :status="$payment->status" />
                    </div>
                    <div class="rp-payment-mini-meta">
                        {{ $payment->payment_date?->format('M d, Y') }} &middot; {{ $methodLabel }}
                    </div>
                    @if($payment->reference_number)
                        <div class="rp-payment-mini-receipt">Ref: {{ $payment->reference_number }}</div>
                    @endif
                    @if($payment->receipt_number)
                        <div class="rp-payment-mini-receipt">Receipt: {{ $payment->receipt_number }}</div>
                    @endif
                    @if($canDownloadReceipt)
                        @php
                            $bookingTotal = (float) $booking->total_amount;
                            $remainingBalance = (float) $booking->remaining_balance;
                            $totalPaid = (float) $booking->paid_amount;
                            $isPartial = $remainingBalance > 0.009;
                            $paymentShare = $bookingTotal > 0
                                ? round(((float) $payment->amount / $bookingTotal) * 100)
                                : 0;
                        @endphp
                        <button
                            type="button"
                            class="rp-payment-download"
                            data-bs-toggle="modal"
                            data-bs-target="#rpGuestReceiptModal"
                            data-receipt-number="{{ $payment->receipt_number ?? 'PAY-'.$payment->id }}"
                            data-booking="{{ $booking->short_number ?? $booking->booking_number }}"
                            data-guest="{{ $booking->guest_name ?? $booking->guest?->user?->name ?? auth()->user()->name }}"
                            data-room="{{ $booking->accommodation->name ?? '—' }}"
                            data-amount="₱{{ number_format($payment->amount, 2) }}"
                            data-booking-total="₱{{ number_format($bookingTotal, 2) }}"
                            data-total-paid="₱{{ number_format($totalPaid, 2) }}"
                            data-remaining="₱{{ number_format(max(0, $remainingBalance), 2) }}"
                            data-is-partial="{{ $isPartial ? '1' : '0' }}"
                            data-payment-note="{{ $isPartial
                                ? ($paymentShare >= 45 && $paymentShare <= 55
                                    ? 'This is a 50% deposit payment. A remaining balance is still due before or on check-in.'
                                    : 'This is a partial payment. A remaining balance is still due before or on check-in.')
                                : 'Booking is fully paid. No remaining balance.' }}"
                            data-method="{{ $methodLabel }}"
                            data-reference="{{ $payment->reference_number ?? '—' }}"
                            data-paid-on="{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}"
                            data-verified="{{ $payment->verified_at?->format('M d, Y g:i A') ?? '—' }}"
                        >
                            <i class="bi bi-download" aria-hidden="true"></i>
                            Download receipt
                        </button>
                    @endif
                </div>
            @empty
                <p class="text-muted small mb-0">No payments recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>
</div>

@if($booking->payments->contains(fn ($p) => ($p->status instanceof \BackedEnum ? $p->status->value : $p->status) === 'verified'))
<div class="modal fade" id="rpGuestReceiptModal" tabindex="-1" aria-labelledby="rpGuestReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-receipt-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpGuestReceiptModalLabel">Payment receipt</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-receipt-card" id="rpGuestReceiptPrint">
                    <div class="rp-receipt-brand">{{ $resortSettings['resort_name'] ?? 'Guanzon Beach' }}</div>
                    <div class="rp-receipt-meta">Payment receipt</div>
                    <hr class="rp-receipt-divider">
                    <div class="rp-receipt-amount" data-receipt-field="amount">₱0.00</div>
                    <div class="rp-receipt-meta text-center mb-2">Verified payment</div>
                    <div class="rp-receipt-balance-note d-none" data-receipt-partial-note>
                        <div class="rp-receipt-balance-note-title" data-receipt-field="payment-note">
                            This is a 50% deposit payment. A remaining balance is still due.
                        </div>
                        <div class="rp-receipt-row mb-0"><span>Booking total</span><strong data-receipt-field="booking-total">—</strong></div>
                        <div class="rp-receipt-row mb-0"><span>Total paid</span><strong data-receipt-field="total-paid">—</strong></div>
                        <div class="rp-receipt-row mb-0"><span>Balance left</span><strong class="rp-receipt-balance-left" data-receipt-field="remaining">—</strong></div>
                    </div>
                    <div class="rp-receipt-row"><span>Receipt</span><strong data-receipt-field="receipt-number">—</strong></div>
                    <div class="rp-receipt-row"><span>Booking</span><strong data-receipt-field="booking">—</strong></div>
                    <div class="rp-receipt-row"><span>Guest</span><strong data-receipt-field="guest">—</strong></div>
                    <div class="rp-receipt-row"><span>Room</span><strong data-receipt-field="room">—</strong></div>
                    <div class="rp-receipt-row"><span>Method</span><strong data-receipt-field="method">—</strong></div>
                    <div class="rp-receipt-row"><span>Reference</span><strong data-receipt-field="reference">—</strong></div>
                    <div class="rp-receipt-row"><span>Paid on</span><strong data-receipt-field="paid-on">—</strong></div>
                    <div class="rp-receipt-row"><span>Verified</span><strong data-receipt-field="verified">—</strong></div>
                    <hr class="rp-receipt-divider">
                    <p class="rp-receipt-thanks">Thank you for staying with us.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-rp-primary" data-rp-print-receipt="#rpGuestReceiptPrint">
                    <i class="bi bi-download me-1" aria-hidden="true"></i>
                    Save to Photos
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('rpGuestReceiptModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    const card = document.getElementById('rpGuestReceiptPrint');
    if (!button || !card) return;

    const setField = (name, value) => {
        const el = card.querySelector(`[data-receipt-field="${name}"]`);
        if (el) el.textContent = value || '—';
    };

    setField('amount', button.dataset.amount);
    setField('receipt-number', button.dataset.receiptNumber);
    setField('booking', button.dataset.booking);
    setField('guest', button.dataset.guest);
    setField('room', button.dataset.room);
    setField('method', button.dataset.method);
    setField('reference', button.dataset.reference);
    setField('paid-on', button.dataset.paidOn);
    setField('verified', button.dataset.verified);
    setField('booking-total', button.dataset.bookingTotal);
    setField('total-paid', button.dataset.totalPaid);
    setField('remaining', button.dataset.remaining);
    setField('payment-note', button.dataset.paymentNote);

    const partialNote = card.querySelector('[data-receipt-partial-note]');
    if (partialNote) {
        partialNote.classList.toggle('d-none', button.dataset.isPartial !== '1');
    }
});
</script>
@endpush
@endif
@endsection
