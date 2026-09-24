@extends('layouts.public')

@section('title', 'Payment Status')

@section('content')
<div class="container rp-public-page-top pb-4">
    <div class="row g-4">
        <div class="col-lg-7 mx-auto">
            <div class="rp-flow-card rp-payment-status-card text-center">
                @if($status === 'confirmed')
                    <i class="bi bi-check-circle display-4 text-success" aria-hidden="true"></i>
                    <h1 class="h4 mt-3 mb-2">Payment confirmed</h1>
                    <p class="rp-payment-status-text text-muted mb-3">
                        Your GCash payment
                        @if(!empty($payment))
                            of <strong>₱{{ number_format((float) $payment->amount, 2) }}</strong>
                        @endif
                        was received.
                    </p>
                    <p class="rp-payment-status-text text-muted mb-4">
                        Your booking balance has been updated.
                    </p>
                @elseif($status === 'success')
                    <i class="bi bi-hourglass-split display-4 text-success" aria-hidden="true"></i>
                    <h1 class="h4 mt-3 mb-2">Confirming your payment&hellip;</h1>
                    <p class="rp-payment-status-text text-muted mb-4">
                        GCash says your payment went through. We're confirming it now —
                        your balance will update automatically, usually within a minute.
                    </p>
                @else
                    <i class="bi bi-x-circle display-4 text-danger" aria-hidden="true"></i>
                    <h1 class="h4 mt-3 mb-2">Payment didn't go through</h1>
                    <p class="rp-payment-status-text text-muted mb-4">
                        Your GCash payment wasn't completed. No amount was deducted.
                        You can try again from your booking.
                    </p>
                @endif
                <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-avail-btn-primary rp-payment-status-btn">
                    Back to Booking
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
