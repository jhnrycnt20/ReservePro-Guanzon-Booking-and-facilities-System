@extends('layouts.dashboard')

@section('title', 'My Reservations')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'My Reservations')
@section('page_subtitle', 'Track reservation status through the full stay lifecycle')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Accommodation</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>{{ $booking->accommodation->name }}</td>
                        <td>{{ $booking->check_in_date->format('M d, Y') }}</td>
                        <td>{{ $booking->check_out_date->format('M d, Y') }}</td>
                        <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                        <td>₱{{ number_format($booking->remaining_balance, 2) }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td><a href="{{ route('guest.bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links'))
        <div class="mt-3">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
