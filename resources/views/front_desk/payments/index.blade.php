@extends('layouts.dashboard')

@section('title', 'Payment Verification')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Payments')
@section('page_subtitle', 'Verify pending guest payments')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Receipt / Ref</th>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->receipt_number ?? $payment->reference_number ?? '—' }}</td>
                        <td>{{ $payment->booking->booking_number ?? '—' }}</td>
                        <td>{{ $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—' }}</td>
                        <td>₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</td>
                        <td>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</td>
                        <td><x-status-badge :status="$payment->status" /></td>
                        <td class="text-nowrap">
                            <form method="POST" action="{{ route('front_desk.payments.verify', $payment) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-rp-primary">Verify</button>
                            </form>
                            <form method="POST" action="{{ route('front_desk.payments.reject', $payment) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                            <a href="{{ route('front_desk.payments.receipt', $payment) }}" class="btn btn-sm btn-rp-soft" target="_blank">Receipt</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">No pending payments.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payments, 'links')) {{ $payments->withQueryString()->links() }} @endif
</div>
@endsection
