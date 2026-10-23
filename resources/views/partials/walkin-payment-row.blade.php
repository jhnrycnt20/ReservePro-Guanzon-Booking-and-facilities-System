@php
    $method = $method ?? 'cash';
    $amount = $amount ?? '';
    $reference = $reference ?? '';
    $index = $index ?? 0;
    $needsRef = $method === 'gcash';
@endphp
<div class="rp-walkin-pay-row" data-rp-walkin-pay-row>
    <div class="rp-walkin-pay-field rp-walkin-pay-method-field">
        <label class="form-label">Method</label>
        <select name="payments[{{ $index }}][payment_method]" class="form-select" data-rp-walkin-pay-method required>
            <option value="cash" @selected($method === 'cash')>Cash</option>
            <option value="gcash" @selected($method === 'gcash')>GCash</option>
        </select>
    </div>
    <div class="rp-walkin-pay-field rp-walkin-pay-amount-field">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" min="0.01" name="payments[{{ $index }}][amount]" class="form-control @error('payments.'.$index.'.amount') is-invalid @enderror" value="{{ $amount }}" data-rp-walkin-pay-amount required>
        @error('payments.'.$index.'.amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="rp-walkin-pay-field rp-walkin-pay-ref-field" data-rp-walkin-pay-ref-wrap @if(! $needsRef) hidden @endif>
        <label class="form-label">Reference</label>
        <input type="text" name="payments[{{ $index }}][reference_number]" class="form-control @error('payments.'.$index.'.reference_number') is-invalid @enderror" value="{{ $reference }}" data-rp-walkin-pay-ref placeholder="Required for GCash" @if($needsRef) required @endif>
        @error('payments.'.$index.'.reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="rp-walkin-pay-actions">
        <button type="button" class="rp-walkin-pay-remove" data-rp-walkin-remove-pay title="Remove payment" aria-label="Remove payment">
            <i class="bi bi-trash3" aria-hidden="true"></i>
        </button>
    </div>
</div>
