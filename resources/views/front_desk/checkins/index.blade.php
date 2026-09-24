@extends('layouts.dashboard')

@section('title', 'Check-in')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-in')
@section('page_subtitle', 'Guests already checked in by front desk — use Reservations to check someone in')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@include('partials.list-filters', [
    'dateName' => 'date',
    'dateLabel' => 'Check-in date',
    'searchPlaceholder' => 'Booking Number, Guest, or Room',
    'clearUrl' => route('front_desk.checkins.index'),
])

{{-- Desktop table --}}
<div class="rp-card d-none d-md-block">
    <div class="table-responsive">
        <table class="table align-middle rp-desk-table">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Checked in</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><span class="rp-booking-code" title="{{ $booking->booking_number }}">{{ $booking->short_number }}</span></td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation->name ?? '—' }}</td>
                        <td>
                            {{ $booking->checkIn?->checked_in_at?->format('M d, Y g:i A')
                                ?? ($booking->check_in_date?->format('M d, Y').' · 2:00 PM') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('front_desk.checkins.show', $booking) }}" class="btn btn-sm btn-rp-soft">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No checked-in guests yet. Check guests in from Reservations.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>

{{-- Mobile cards --}}
<div class="d-md-none">
    @forelse($bookings as $booking)
        <div class="rp-mobile-list-card">
            <div class="rp-mobile-list-card__top">
                <span class="rp-booking-code">{{ $booking->short_number }}</span>
            </div>
            <div class="rp-mobile-list-card__title">{{ $booking->guest_name }}</div>
            <div class="rp-mobile-list-card__meta">{{ $booking->accommodation->name ?? '—' }}</div>
            <div class="rp-mobile-list-card__meta">
                {{ $booking->checkIn?->checked_in_at?->format('M d, Y g:i A')
                    ?? ($booking->check_in_date?->format('M d, Y').' · 2:00 PM') }}
            </div>
            <a href="{{ route('front_desk.checkins.show', $booking) }}" class="btn btn-sm btn-rp-soft w-100 mt-2">View</a>
        </div>
    @empty
        <div class="rp-card text-muted">No checked-in guests yet. Check guests in from Reservations.</div>
    @endforelse
    @if(method_exists($bookings, 'links'))
        <div class="mt-3">{{ $bookings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
