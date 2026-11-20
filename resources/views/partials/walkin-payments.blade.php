@php
    $oldPayments = old('payments');
    if (! is_array($oldPayments) || count($oldPayments) < 1) {
        $oldPayments = [[
            'payment_method' => 'cash',
            'amount' => '',
            'reference_number' => '',
        ]];
    }
@endphp

<div class="col-12" data-rp-walkin-payments>
    <div class="rp-walkin-pay-head">
        <div>
            <div class="rp-walkin-section-title">
                <i class="bi bi-wallet2" aria-hidden="true"></i>
                <span>Payments</span>
            </div>
        </div>
        <button type="button" class="btn btn-sm rp-walkin-add-pay" data-rp-walkin-add-pay>
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Add payment
        </button>
    </div>

    <div class="rp-walkin-pay-rows" data-rp-walkin-pay-rows>
        @foreach($oldPayments as $index => $row)
            @include('partials.walkin-payment-row', [
                'index' => $index,
                'method' => $row['payment_method'] ?? 'cash',
                'amount' => $row['amount'] ?? '',
                'reference' => $row['reference_number'] ?? '',
            ])
        @endforeach
    </div>

    <template data-rp-walkin-pay-template>
        @include('partials.walkin-payment-row', [
            'index' => '__INDEX__',
            'method' => 'cash',
            'amount' => '',
            'reference' => '',
        ])
    </template>

    @error('payments')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
