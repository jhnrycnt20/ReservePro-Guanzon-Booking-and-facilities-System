@extends('layouts.dashboard')

@section('title', 'Check-in')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-in')
@section('page_subtitle', 'Fully paid guests — auto check-in from 2:00 PM on arrival day')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@include('partials.list-filters', [
    'dateName' => 'date',
    'dateLabel' => 'Check-in date',
    'searchPlaceholder' => 'Booking #, guest, or room',
    'clearUrl' => route('front_desk.checkins.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><span class="rp-booking-code" title="{{ $booking->booking_number }}">{{ $booking->short_number }}</span></td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation->name ?? '—' }}</td>
                        <td>{{ $booking->check_in_date?->format('M d, Y') }} · 2:00 PM</td>
                        <td>
                            <a href="{{ route('front_desk.checkins.show', $booking) }}" class="btn btn-sm btn-rp-primary">View details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No fully paid stays ready for check-in.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>
@endsection
