@extends('layouts.dashboard')

@section('title', 'Payments')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Payment History')
@section('page_subtitle', 'Payments submitted for your reservations')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            @if($payment->booking)
                                <a href="{{ route('guest.bookings.show', $payment->booking) }}">{{ $payment->booking->booking_number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</td>
                        <td>{{ $payment->reference_number ?? '—' }}</td>
                        <td>{{ $payment->payment_date?->format('M d, Y') ?? $payment->created_at?->format('M d, Y') }}</td>
                        <td><x-status-badge :status="$payment->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payments, 'links')) {{ $payments->withQueryString()->links() }} @endif
</div>
@endsection
