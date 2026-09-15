@extends('layouts.dashboard')

@section('title', 'Payment Verification')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Payments')
@section('page_subtitle', 'Review payment details and screenshots')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
<div class="rp-card mb-3">
    <form method="GET" class="row g-2 align-items-end" data-rp-live-filter>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" data-rp-live-filter-change>
                <option value="pending" @selected(($status ?? 'pending') === 'pending')>Pending</option>
                <option value="verified" @selected(($status ?? '') === 'verified')>Verified</option>
                <option value="rejected" @selected(($status ?? '') === 'rejected')>Rejected</option>
                <option value="all" @selected(($status ?? '') === 'all')>All</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Booking #, guest, or reference" data-rp-live-filter-q autocomplete="off">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-rp-primary w-100">Filter</button>
        </div>
    </form>
</div>

<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Proof</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->reference_number ?? $payment->receipt_number ?? '—' }}</td>
                        <td>{{ $payment->booking->short_number ?? '—' }}</td>
                        <td>{{ $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—' }}</td>
                        <td>₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</td>
                        <td>
                            @if($payment->proof_url)
                                <span class="badge text-bg-success">Yes</span>
                            @else
                                <span class="text-muted">No</span>
                            @endif
                        </td>
                        <td>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</td>
                        <td><x-status-badge :status="$payment->status" /></td>
                        <td class="text-nowrap">
                            <a href="{{ route('front_desk.payments.show', $payment) }}" class="btn btn-sm btn-rp-primary">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payments, 'links')) {{ $payments->links() }} @endif
</div>
@endsection
