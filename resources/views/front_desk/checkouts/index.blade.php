@extends('layouts.dashboard')

@section('title', 'Check-out')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Check-out')
@section('page_subtitle', 'Checked-in guests ready for departure')
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
                    <th>Check-out</th>
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
                        <td>{{ $booking->check_out_date?->format('M d, Y') }}</td>
                        <td>₱{{ number_format($booking->remaining_balance, 2) }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td>
                            <a href="{{ route('front_desk.checkouts.show', $booking) }}" class="btn btn-sm btn-rp-primary">Check out</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No checked-in bookings awaiting check-out.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($bookings, 'links')) {{ $bookings->withQueryString()->links() }} @endif
</div>
@endsection
