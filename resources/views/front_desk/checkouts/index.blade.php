@extends('layouts.dashboard')

@section('title', 'Check-out')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-out')
@section('page_subtitle', 'Shown from 12:00 noon on check-out day — auto check-out when balance is zero')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@include('partials.list-filters', [
    'dateName' => 'date',
    'dateLabel' => 'Check-out date',
    'searchPlaceholder' => 'Booking #, guest, or room',
    'clearUrl' => route('front_desk.checkouts.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
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
                        <td>
                            <a href="{{ route('front_desk.reservations.show', $booking) }}" class="btn btn-sm btn-rp-primary">View details</a>
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
@endsection
