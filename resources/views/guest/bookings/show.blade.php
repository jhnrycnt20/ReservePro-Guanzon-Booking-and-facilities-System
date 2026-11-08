@extends('layouts.public')

@section('title', 'Reservation '.$booking->booking_number)
@section('body_class', 'rp-page-reservation-detail')

@section('content')
@php
    $bookingStatus = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
    $checkInTime = \Carbon\Carbon::createFromFormat('H:i', (string) config('resort.check_in_time', '14:00'))->format('g:i A');
    $bookingGallery = $booking->accommodation->gallery_urls;
    $nights = max(1, $booking->check_in_date->diffInDays($booking->check_out_date));
    $discountAmount = (float) ($booking->discount_amount ?? 0);
    $staySubtotal = (float) ($booking->original_amount ?? ((float) $booking->total_amount + $discountAmount));
    $additionalCharges = max(0, (float) $booking->total_amount - max(0, $staySubtotal - $discountAmount));
    $nightlyRate = $nights > 0 ? $staySubtotal / $nights : 0;
@endphp

<div class="container rp-public-page-top rp-reservation-page pb-5">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Reservations</a>

    <div class="row g-4 rp-reservation-layout">
        <div class="col-lg-6">
            <section class="rp-reservation-stay-card" aria-label="Accommodation details">
                <div class="rp-reservation-gallery" data-rp-booking-gallery>
                    <div class="rp-reservation-gallery-main">
                        <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}" data-rp-reservation-main>
                        <button type="button" class="rp-reservation-photo-mark" data-rp-reservation-lightbox-open aria-label="Open photo gallery">
                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                        </button>
                    </div>
                    @if(count($bookingGallery) > 1)
                        <div class="rp-reservation-gallery-rail" aria-label="More accommodation photos">
                            @foreach($bookingGallery as $index => $photoUrl)
                                <button type="button" class="rp-reservation-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}" data-rp-reservation-photo="{{ $photoUrl }}" aria-label="Show accommodation photo {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                                    <img src="{{ $photoUrl }}" alt="">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($booking->special_requests || $booking->rejection_reason)
                    <div class="rp-reservation-notes">
                        @if($booking->special_requests)
                            <h3>Special requests</h3>
                            <p>{{ $booking->special_requests }}</p>
                        @endif
                        @if($booking->rejection_reason)
                            <div class="alert alert-danger {{ $booking->special_requests ? 'mt-3' : '' }} mb-0">{{ $booking->rejection_reason }}</div>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <div class="col-lg-6">
            <section class="rp-reservation-summary-card" aria-labelledby="rpReservationSummaryTitle">
                <div class="rp-reservation-total">
                    <div class="rp-reservation-title-amenities">
                        <h2 id="rpReservationSummaryTitle">{{ $booking->accommodation->name }}</h2>
                        <span class="rp-reservation-title-meta">{{ $booking->accommodation->type->name ?? 'Accommodation' }}@if(filled($booking->accommodation->number)) · Unit {{ $booking->accommodation->number }}@endif · {{ $booking->booking_number }}</span>
                    </div>
                </div>

                <div class="rp-reservation-dates">
                    <div class="rp-reservation-date-item">
                        <span class="rp-reservation-date-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                        <div><span>Check-in</span><strong>{{ $booking->check_in_date->format('M j, Y') }}</strong><small>From {{ $checkInTime }}</small></div>
                    </div>
                    <div class="rp-reservation-date-divider" aria-hidden="true"></div>
                    <div class="rp-reservation-date-item">
                        <span class="rp-reservation-date-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                        <div><span>Check-out</span><strong>{{ $booking->check_out_date->format('M j, Y') }}</strong><small>By {{ \Carbon\Carbon::createFromFormat('H:i', (string) config('resort.check_out_time', '12:00'))->format('g:i A') }}</small></div>
                    </div>
                </div>

                <div class="rp-reservation-guest-line">
                    <span><i class="bi bi-people" aria-hidden="true"></i> Guests</span>
                    <strong>{{ $booking->adults }} {{ \Illuminate\Support\Str::plural('adult', $booking->adults) }}{{ (int) $booking->children > 0 ? ', '.$booking->children.' '.\Illuminate\Support\Str::plural('child', $booking->children) : '' }}</strong>
                </div>

                <div class="rp-reservation-price-breakdown" aria-label="Price breakdown">
                    <div><span>Stay (&#8369;{{ number_format($nightlyRate, 2) }} × {{ $nights }} {{ \Illuminate\Support\Str::plural('night', $nights) }})</span><strong>&#8369;{{ number_format($staySubtotal, 2) }}</strong></div>
                    @if($discountAmount > 0)
                        <div class="is-discount"><span>Discount{{ $booking->promo_code ? ' · '.$booking->promo_code : '' }}</span><strong>−&#8369;{{ number_format($discountAmount, 2) }}</strong></div>
                    @endif
                    @if($additionalCharges > 0.009)
                        <div><span>Additional charges</span><strong>&#8369;{{ number_format($additionalCharges, 2) }}</strong></div>
                    @endif
                    <div class="is-total"><span>Total stay · {{ $nights }} {{ \Illuminate\Support\Str::plural('night', $nights) }}</span><strong>&#8369;{{ number_format((float) $booking->total_amount, 2) }}</strong></div>
                    <div><span>Paid to date</span><strong>&#8369;{{ number_format((float) $booking->paid_amount, 2) }}</strong></div>
                    <div class="is-balance"><span>Balance due</span><strong>&#8369;{{ number_format((float) $booking->remaining_balance, 2) }}</strong></div>
                </div>

                <button type="button" class="rp-transaction-history-trigger" data-bs-toggle="modal" data-bs-target="#rpTransactionHistoryModal">
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    View transaction history
                    <span>{{ $booking->payments->count() }}</span>
                </button>

                <div class="rp-reservation-actions">
                    @if(in_array($bookingStatus, ['pending', 'approved', 'checked_in']) && (float) $booking->remaining_balance > 0.009)
                        <a href="{{ route('guest.payments.create', ['booking' => $booking, 'fresh' => 1]) }}" class="rp-reservation-pay-button">Proceed to Billing</a>
                    @endif
                    @if(in_array($bookingStatus, ['pending', 'approved']))
                        <form method="POST" action="{{ route('guest.bookings.cancel', $booking) }}">
                            @csrf
                            <button type="submit" class="rp-reservation-secondary-button is-danger" data-rp-confirm-click="Cancel this reservation?">Cancel Reservation</button>
                        </form>
                    @endif
                    @if(in_array($bookingStatus, ['approved', 'checked_in', 'checked_out']))
                        <a href="{{ route('guest.incidents.create', ['booking_id' => $booking->id]) }}" class="rp-reservation-secondary-button">Report an Issue</a>
                    @endif
                    @if($bookingStatus === 'checked_out' && !$booking->feedback)
                        <a href="{{ route('guest.feedback.create_booking', $booking) }}" class="rp-reservation-secondary-button">Leave Feedback</a>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade rp-reservation-lightbox" id="rpReservationLightbox" tabindex="-1" aria-label="Reservation photo gallery" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered rp-lightbox-dialog">
        <div class="modal-content rp-lightbox-modal">
            <div class="modal-body">
                <div class="rp-lightbox-stage" data-rp-reservation-lightbox-stage>
                    @if(count($bookingGallery) > 1)
                        <button type="button" class="rp-lightbox-nav rp-lightbox-prev" data-rp-reservation-lightbox-prev aria-label="Previous photo">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>
                    @endif
                    <img src="{{ $booking->accommodation->image_url }}" alt="{{ $booking->accommodation->name }}" class="rp-lightbox-image" data-rp-reservation-lightbox-image>
                    @if(count($bookingGallery) > 1)
                        <button type="button" class="rp-lightbox-nav rp-lightbox-next" data-rp-reservation-lightbox-next aria-label="Next photo">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                    @endif
                    <span class="rp-lightbox-counter" data-rp-reservation-lightbox-counter>1 / {{ max(1, count($bookingGallery)) }}</span>
                </div>
                @if(count($bookingGallery) > 1)
                    <div class="rp-lightbox-thumbnails" aria-label="Choose a reservation photo">
                        @foreach($bookingGallery as $index => $photoUrl)
                            <button type="button" class="rp-lightbox-thumb {{ $index === 0 ? 'is-active' : '' }}" data-rp-reservation-lightbox-thumb="{{ $index }}" aria-label="Show photo {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                                <img src="{{ $photoUrl }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade rp-transaction-history-modal" id="rpTransactionHistoryModal" tabindex="-1" aria-labelledby="rpTransactionHistoryTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="rpTransactionHistoryTitle">Transaction history</h2>
                </div>
            </div>
            <div class="modal-body">
                @forelse($booking->payments as $payment)
                    @php
                        $historyStatus = $payment->status instanceof \BackedEnum ? $payment->status->value : (string) $payment->status;
                        $historyMethod = str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : (string) $payment->payment_method));
                    @endphp
                    <article class="rp-transaction-history-item">
                        <div class="rp-transaction-history-item-top">
                            <div>
                                <strong>&#8369;{{ number_format((float) $payment->amount, 2) }}</strong>
                                <span>{{ $payment->payment_date?->format('M d, Y · g:i A') ?? 'Date unavailable' }}</span>
                            </div>
                        </div>
                        <div class="rp-transaction-history-method"><i class="bi bi-wallet2" aria-hidden="true"></i>{{ $historyMethod }}</div>
                        @if($payment->gateway === 'demo')
                            <div class="rp-transaction-history-demo"><i class="bi bi-info-circle" aria-hidden="true"></i> Sample transaction · no real charge</div>
                        @endif
                        @if($payment->reference_number)
                            <div class="rp-transaction-history-reference">Reference <span>{{ $payment->reference_number }}</span></div>
                        @endif
                        @if($payment->receipt_number)
                            <div class="rp-transaction-history-reference">Receipt <span>{{ $payment->receipt_number }}</span></div>
                        @endif
                    </article>
                @empty
                    <div class="rp-transaction-history-empty">
                        <span><i class="bi bi-receipt" aria-hidden="true"></i></span>
                        <strong>No transactions yet</strong>
                        <p>Payments for this reservation will appear here.</p>
                    </div>
                @endforelse
            </div>
            <div class="modal-footer"><button type="button" class="rp-transaction-history-close" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

@endsection
