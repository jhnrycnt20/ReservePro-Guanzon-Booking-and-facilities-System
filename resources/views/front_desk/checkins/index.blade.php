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
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 16%;">Booking</th>
                    <th style="width: 22%;">Guest</th>
                    <th style="width: 18%;">Room</th>
                    <th style="width: 28%;">Check-in</th>
                    <th style="width: 16%;"></th>
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
@endsection
