<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</title>
    <style>
        :root {
            --ink: #18352f;
            --muted: #6b7c75;
            --line: #e0e9e4;
            --accent: #0f6b45;
            --soft: #f2f6f4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--soft);
            color: var(--ink);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }
        .rp-receipt-shell {
            width: min(100%, 520px);
            margin: 0 auto;
        }
        .rp-receipt-actions {
            display: flex;
            gap: .65rem;
            margin-top: 1rem;
        }
        .rp-receipt-actions a,
        .rp-receipt-actions button {
            flex: 1;
            appearance: none;
            min-height: 46px;
            border-radius: 11px;
            padding: .7rem 1rem;
            font-size: .9rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 150ms ease-out, border-color 150ms ease-out, color 150ms ease-out, transform 150ms ease-out;
        }
        .rp-receipt-download {
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
        }
        .rp-receipt-download:hover { background: #0b5638; border-color: #0b5638; }
        .rp-receipt-back {
            background: #fff;
            border: 1px solid var(--line);
            color: #31594a;
        }
        .rp-receipt-back:hover { background: #f7fbf8; border-color: #b9d6c5; color: var(--accent); }
        .rp-receipt-actions :is(a, button):active { transform: scale(.985); }
        .rp-receipt-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 1.65rem;
            box-shadow: 0 18px 50px rgba(24, 53, 47, .09);
        }
        .rp-receipt-brand {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -.025em;
        }
        .rp-receipt-meta {
            color: var(--muted);
            font-size: .82rem;
            margin-top: .15rem;
        }
        .rp-receipt-divider {
            border: none;
            border-top: 1px solid var(--line);
            margin: 1.2rem 0;
        }
        .rp-receipt-row {
            display: grid;
            grid-template-columns: minmax(5.5rem, .7fr) minmax(0, 1.3fr);
            align-items: baseline;
            gap: .8rem;
            padding: .7rem 0;
            border-bottom: 1px solid #edf2ef;
            font-size: .88rem;
        }
        .rp-receipt-row span {
            color: var(--muted);
        }
        .rp-receipt-row strong {
            text-align: right;
            font-weight: 600;
            color: var(--ink);
            overflow-wrap: anywhere;
        }
        .rp-receipt-amount {
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: -.035em;
            line-height: 1.1;
            margin: .5rem 0 .2rem;
        }
        .rp-receipt-thanks {
            text-align: center;
            color: var(--muted);
            font-size: .82rem;
            margin: 0;
        }
        .rp-receipt-demo-notice {
            margin: 1.1rem 0 0;
            padding: .7rem .8rem;
            border: 1px solid #cfe5d8;
            border-radius: 11px;
            background: #f0f8f3;
            color: #176b4c;
            font-size: .8rem;
            line-height: 1.45;
        }
        .rp-receipt-card > .rp-receipt-meta[style] {
            display: table;
            margin: 0 auto 1.1rem !important;
            padding: .28rem .65rem;
            border-radius: 999px;
            background: #e9f5ed;
            color: var(--accent);
            font-size: .74rem;
            font-weight: 600;
        }
        .rp-receipt-row:last-of-type { border-bottom: 0; }
        @media print {
            body { display: block; min-height: 0; background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .rp-receipt-card {
                box-shadow: none;
                border: none;
                border-radius: 0;
                max-width: none;
            }
        }
        @media (max-width: 480px) {
            body { padding: 1rem .75rem; }
            .rp-receipt-card { padding: 1.25rem 1rem; border-radius: 16px; }
            .rp-receipt-brand { font-size: 1.08rem; }
            .rp-receipt-amount { font-size: 1.75rem; }
            .rp-receipt-row { grid-template-columns: minmax(4.5rem, .65fr) minmax(0, 1.35fr); gap: .55rem; font-size: .82rem; }
        }
    </style>
</head>
<body>
@php
    $methodRaw = $payment->payment_method instanceof \BackedEnum
        ? $payment->payment_method->value
        : ($payment->payment_method ?: 'other');
    $method = str_replace('_', ' ', ucfirst((string) $methodRaw));
    $guestName = $payment->booking?->guest_name
        ?? $payment->booking?->guest?->user?->name
        ?? '—';
    $bookingCode = $payment->booking?->short_number
        ?? $payment->booking?->booking_number
        ?? '—';
    $bookingTotal = (float) ($payment->booking?->total_amount ?? 0);
    $remainingBalance = (float) ($payment->booking?->remaining_balance ?? 0);
    $totalPaid = (float) ($payment->booking?->paid_amount ?? 0);
    $isPartial = $remainingBalance > 0.009;
    $paymentShare = $bookingTotal > 0 ? round(((float) $payment->amount / $bookingTotal) * 100) : 0;
@endphp
<div class="rp-receipt-shell">
    <div class="rp-receipt-card">
        <div class="rp-receipt-brand">{{ $resortSettings['resort_name'] ?? 'Guanzon Beach' }}</div>

        @if($payment->gateway === 'demo')
            <div class="rp-receipt-demo-notice"><strong>Sample payment only.</strong> No real GCash transaction or charge was made.</div>
        @endif

        <hr class="rp-receipt-divider">

        <div class="rp-receipt-amount">₱{{ number_format($payment->amount, 2) }}</div>
        <div class="rp-receipt-meta" style="text-align:center;margin-bottom:.75rem;">{{ $payment->gateway === 'demo' ? 'Sample success' : 'Verified payment' }}</div>

        @if($isPartial)
            <div style="background:#f7f4ea;border:1px solid #ead9a8;border-radius:12px;padding:.75rem .85rem;margin:0 0 .9rem;">
                <div style="font-size:.78rem;line-height:1.35;color:#7a5b12;font-weight:600;margin-bottom:.55rem;">
                    {{ $paymentShare >= 45 && $paymentShare <= 55
                        ? 'This is a 50% deposit payment. A remaining balance is still due before or on check-in.'
                        : 'This is a partial payment. A remaining balance is still due before or on check-in.' }}
                </div>
                <div class="rp-receipt-row"><span>Booking total</span><strong>₱{{ number_format($bookingTotal, 2) }}</strong></div>
                <div class="rp-receipt-row"><span>Total paid</span><strong>₱{{ number_format($totalPaid, 2) }}</strong></div>
                <div class="rp-receipt-row"><span>Balance left</span><strong style="color:#b42318;">₱{{ number_format(max(0, $remainingBalance), 2) }}</strong></div>
            </div>
        @endif

        <div class="rp-receipt-row"><span>Receipt</span><strong>{{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</strong></div>
        <div class="rp-receipt-row"><span>Booking</span><strong>{{ $bookingCode }}</strong></div>
        <div class="rp-receipt-row"><span>Guest</span><strong>{{ $guestName }}</strong></div>
        @if($payment->booking?->accommodation)
            <div class="rp-receipt-row"><span>Room</span><strong>{{ $payment->booking->accommodation->name }}</strong></div>
        @endif
        <div class="rp-receipt-row"><span>Method</span><strong>{{ $method }}</strong></div>
        <div class="rp-receipt-row"><span>Reference</span><strong>{{ $payment->reference_number ?? '—' }}</strong></div>
        <div class="rp-receipt-row"><span>Paid on</span><strong>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</strong></div>
        @if($payment->verified_at)
            <div class="rp-receipt-row"><span>Verified</span><strong>{{ $payment->verified_at->format('M d, Y g:i A') }}</strong></div>
        @endif

        <hr class="rp-receipt-divider">
        <p class="rp-receipt-thanks">Thank you for staying with us.</p>
    </div>

    <div class="rp-receipt-actions no-print">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('guest.bookings.show', $payment->booking) }}" class="rp-receipt-back">Back</a>
        <button type="button" class="rp-receipt-download" onclick="window.print()">Download receipt</button>
    </div>
</div>
</body>
</html>
