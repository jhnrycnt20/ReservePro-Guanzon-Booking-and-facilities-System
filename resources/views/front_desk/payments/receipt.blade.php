<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt — {{ $payment->receipt_number ?? $payment->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f4ef; font-family: Georgia, "Times New Roman", serif; }
        .receipt {
            max-width: 480px;
            margin: 2rem auto;
            background: #fff;
            border: 1px solid #ddd;
            padding: 2rem;
        }
        .receipt h1 { font-size: 1.4rem; margin-bottom: .25rem; }
        .meta { color: #666; font-size: .9rem; }
        .row-line { display: flex; justify-content: space-between; margin: .4rem 0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .receipt { border: none; margin: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1>ReservePro</h1>
                <div class="meta">Payment Receipt</div>
            </div>
            <button class="btn btn-sm btn-dark no-print" onclick="window.print()">Print</button>
        </div>
        <hr>
        <div class="row-line"><span>Receipt #</span><strong>{{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</strong></div>
        <div class="row-line"><span>Booking</span><strong>{{ $payment->booking->booking_number ?? '—' }}</strong></div>
        <div class="row-line"><span>Guest</span><strong>{{ $payment->booking->guest_name ?? $payment->booking?->guest?->user?->name ?? '—' }}</strong></div>
        <div class="row-line"><span>Amount</span><strong>₱{{ number_format($payment->amount, 2) }}</strong></div>
        <div class="row-line">
            <span>Method</span>
            <strong>{{ str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : $payment->payment_method)) }}</strong>
        </div>
        <div class="row-line"><span>Reference</span><strong>{{ $payment->reference_number ?? '—' }}</strong></div>
        <div class="row-line"><span>Payment date</span><strong>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</strong></div>
        <div class="row-line">
            <span>Status</span>
            <strong>{{ str_replace('_', ' ', ucfirst($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status)) }}</strong>
        </div>
        @if($payment->verified_at)
            <div class="row-line"><span>Verified at</span><strong>{{ $payment->verified_at->format('M d, Y g:i A') }}</strong></div>
        @endif
        <hr>
        <p class="meta mb-0 text-center">Thank you for staying with us.</p>
    </div>
</body>
</html>
