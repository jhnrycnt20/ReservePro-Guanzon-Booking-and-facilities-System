@extends('layouts.dashboard')

@section('title', 'Check-in')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-in')
@section('page_subtitle', 'Approved bookings ready for arrival')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->accommodation->name ?? '—' }}</td>
                        <td>{{ $booking->check_in_date?->format('M d, Y') }}</td>
                        <td>₱{{ number_format($booking->remaining_balance, 2) }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td>
                            <a href="{{ route('front_desk.checkins.show', $booking) }}" class="btn btn-sm btn-rp-primary">Check in</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No approved bookings awaiting check-in.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>
@endsection
