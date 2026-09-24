@extends('layouts.dashboard')

@section('title', 'Check-out')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-out')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@include('partials.list-filters', [
    'dateName' => 'date',
    'dateLabel' => 'Check-out date',
    'searchPlaceholder' => 'Booking Number, Guest, or Room',
    'clearUrl' => route('front_desk.checkouts.index'),
])

<div class="rp-card d-none d-md-block">
    <div class="table-responsive">
        <table class="table align-middle rp-desk-table">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-out</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><span class="rp-booking-code" title="{{ $booking->booking_number }}">{{ $booking->short_number }}</span></td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation->name ?? '—' }}</td>
                        <td>{{ $booking->check_out_date?->format('M d, Y') }} · 12:00 PM</td>
                        <td class="text-end">
                            <a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-soft">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No check-outs due yet (opens 12:00 noon on departure day).</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>

<div class="d-md-none">
    @forelse($bookings as $booking)
        <div class="rp-mobile-list-card">
            <div class="rp-mobile-list-card__top">
                <span class="rp-booking-code">{{ $booking->short_number }}</span>
            </div>
            <div class="rp-mobile-list-card__title">{{ $booking->guest_name }}</div>
            <div class="rp-mobile-list-card__meta">{{ $booking->accommodation->name ?? '—' }}</div>
            <div class="rp-mobile-list-card__meta">{{ $booking->check_out_date?->format('M d, Y') }} · 12:00 PM</div>
            <a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-soft w-100 mt-2">View</a>
        </div>
    @empty
        <div class="rp-card text-muted">No check-outs due yet (opens 12:00 noon on departure day).</div>
    @endforelse
    @if(method_exists($bookings, 'links'))
        <div class="mt-3">{{ $bookings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
