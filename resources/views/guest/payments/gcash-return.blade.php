@extends('layouts.public')

@section('title', 'Payment Status')

@section('content')
<div class="container rp-public-page-top pb-4">
    <div class="row g-4">
        <div class="col-lg-7 mx-auto">
            <div class="rp-flow-card text-center">
                @if($status === 'success')
                    <i class="bi bi-hourglass-split display-4 text-success"></i>
                    <h1 class="h4 mt-3">Confirming your payment&hellip;</h1>
                    <p class="text-muted">
                        GCash says your payment went through. We're confirming it with GCash now —
                        your balance will update automatically once that's done, usually within a minute.
                    </p>
                @else
                    <i class="bi bi-x-circle display-4 text-danger"></i>
                    <h1 class="h4 mt-3">Payment didn't go through</h1>
                    <p class="text-muted">
                        Your GCash payment wasn't completed (it may have been cancelled or declined).
                        No amount was deducted from your balance. You can try again below.
                    </p>
                @endif
                <a href="{{ route('guest.bookings.show', $booking) }}" class="rp-avail-btn-primary d-inline-block mt-2">Back to Booking</a>
            </div>
        </div>
    </div>
</div>
@endsection
