<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number ?? 'PAY-'.$payment->id }}</title>
    <style>
        :root {
            --ink: #1f2a28;
            --muted: #6b7a77;
            --line: #d9e1df;
            --accent: #5a6766;
            --soft: #eef2f1;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--soft);
            color: var(--ink);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            padding: 1.5rem 1rem 2.5rem;
        }
        .rp-receipt-shell {
            max-width: 440px;
            margin: 0 auto;
        }
        .rp-receipt-actions {
            display: flex;
            gap: .6rem;
            margin-bottom: 1rem;
        }
        .rp-receipt-actions a,
        .rp-receipt-actions button {
            flex: 1;
            appearance: none;
            border-radius: 10px;
            padding: .7rem 1rem;
            font-size: .9rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .rp-receipt-download {
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
        }
        .rp-receipt-back {
            background: #fff;
            border: 1px solid var(--line);
            color: var(--ink);
        }
        .rp-receipt-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.5rem 1.35rem;
            box-shadow: 0 8px 24px rgba(90, 103, 102, 0.08);
        }
        .rp-receipt-brand {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .rp-receipt-meta {
            color: var(--muted);
            font-size: .82rem;
            margin-top: .15rem;
        }
        .rp-receipt-divider {
            border: none;
            border-top: 1px solid var(--line);
            margin: 1rem 0;
        }
        .rp-receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin: .55rem 0;
            font-size: .92rem;
        }
        .rp-receipt-row span {
            color: var(--muted);
        }
        .rp-receipt-row strong {
            text-align: right;
            font-weight: 600;
            color: var(--ink);
        }
        .rp-receipt-amount {
            font-size: 1.35rem;
            font-weight: 700;
            text-align: center;
            margin: .35rem 0 .15rem;
        }
        .rp-receipt-thanks {
            text-align: center;
            color: var(--muted);
            font-size: .82rem;
            margin: 0;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .rp-receipt-card {
                box-shadow: none;
                border: none;
                border-radius: 0;
                max-width: none;
            }
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
    <div class="rp-receipt-actions no-print">
        <button type="button" class="rp-receipt-download" onclick="window.print()">Download receipt</button>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('guest.bookings.show', $payment->booking) }}" class="rp-receipt-back">Back</a>
    </div>

    <div class="rp-receipt-card">
        <div class="rp-receipt-brand">{{ $resortSettings['resort_name'] ?? 'Guanzon Beach' }}</div>
        <div class="rp-receipt-meta">Payment receipt</div>

        <hr class="rp-receipt-divider">

        <div class="rp-receipt-amount">₱{{ number_format($payment->amount, 2) }}</div>
        <div class="rp-receipt-meta" style="text-align:center;margin-bottom:.75rem;">Verified payment</div>

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
</div>
</body>
</html>
