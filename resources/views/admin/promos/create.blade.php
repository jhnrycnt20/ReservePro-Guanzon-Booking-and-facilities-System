@extends('layouts.dashboard')

@section('title', 'Create Promo')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Create Promo')
@section('page_subtitle', 'Pick rooms, set discount %, preview prices, then generate a code')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<form method="POST" action="{{ route('admin.promos.store') }}" id="rpPromoForm" data-preview-url="{{ route('admin.promos.preview') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="rp-card mb-3">
                <h2 class="h5 mb-3">1. Choose accommodations</h2>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="applies_to_all" value="1" id="appliesToAll" @checked(old('applies_to_all'))>
                    <label class="form-check-label fw-semibold" for="appliesToAll">Apply to all accommodations</label>
                </div>
                <div id="rpPromoAccommodationList" class="rp-promo-accommodation-list @error('accommodation_ids') is-invalid @enderror">
                    @foreach($accommodations as $item)
                        <label class="rp-promo-accommodation-item">
                            <input
                                type="checkbox"
                                name="accommodation_ids[]"
                                value="{{ $item->id }}"
                                class="form-check-input rp-promo-accommodation-check"
                                data-rate="{{ $item->rate }}"
                                data-name="{{ $item->name }}"
                                @checked(collect(old('accommodation_ids', []))->contains($item->id))
                            >
                            <span>
                                <strong>{{ $item->name }}</strong>
                                <span class="text-muted small d-block">{{ $item->number }} · ₱{{ number_format((float) $item->rate, 2) }} / night</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('accommodation_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="rp-card">
                <h2 class="h5 mb-3">2. Promo code details</h2>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label" for="promoName">Promo name (optional)</label>
                        <input type="text" name="name" id="promoName" class="form-control" value="{{ old('name') }}" placeholder="Summer stay discount">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="promoCode">Promo code</label>
                        <input type="text" name="code" id="promoCode" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $generatedCode) }}" maxlength="32" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-rp-soft w-100" id="rpGeneratePromoCode" data-generate-url="{{ route('admin.promos.create') }}">Regenerate</button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="startsAt">Starts (optional)</label>
                        <input type="datetime-local" name="starts_at" id="startsAt" class="form-control" value="{{ old('starts_at') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="endsAt">Ends (optional)</label>
                        <input type="datetime-local" name="ends_at" id="endsAt" class="form-control" value="{{ old('ends_at') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="usageLimit">Usage limit (optional)</label>
                        <input type="number" min="1" name="usage_limit" id="usageLimit" class="form-control" value="{{ old('usage_limit') }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="isActive">Active immediately</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="rp-card sticky-top" style="top: 1rem;">
                <h2 class="h5 mb-3">3. Promo percentage & price preview</h2>
                <div class="mb-3">
                    <label class="form-label" for="discountPercent">Promo percentage</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="1" max="100" name="discount_percent" id="discountPercent" class="form-control @error('discount_percent') is-invalid @enderror" value="{{ old('discount_percent', 10) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('discount_percent')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">Original rate → discounted rate after this percentage is applied.</div>
                </div>
                <div id="rpPromoPreview" class="rp-promo-preview">
                    <div class="text-muted small">Select accommodations and enter a percentage to preview prices.</div>
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 mt-3">Save promo code</button>
                <a href="{{ route('admin.promos.index') }}" class="btn btn-rp-soft w-100 mt-2">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('rpPromoForm');
    if (!form) return;

    const previewUrl = form.dataset.previewUrl;
    const previewEl = document.getElementById('rpPromoPreview');
    const allToggle = document.getElementById('appliesToAll');
    const list = document.getElementById('rpPromoAccommodationList');
    const percentInput = document.getElementById('discountPercent');
    const codeInput = document.getElementById('promoCode');
    const regenerateBtn = document.getElementById('rpGeneratePromoCode');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let timer = null;

    const peso = (n) => `₱${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const syncAllToggle = () => {
        const disabled = Boolean(allToggle?.checked);
        list?.classList.toggle('is-disabled', disabled);
        list?.querySelectorAll('.rp-promo-accommodation-check').forEach((el) => {
            el.disabled = disabled;
        });
    };

    const renderPreview = (rows) => {
        if (!previewEl) return;
        if (!rows.length) {
            previewEl.innerHTML = '<div class="text-muted small">Select accommodations and enter a percentage to preview prices.</div>';
            return;
        }
        previewEl.innerHTML = rows.map((row) => `
            <div class="rp-promo-preview-row">
                <div>
                    <div class="fw-semibold">${row.name}</div>
                    <div class="small text-muted">${row.number || ''}</div>
                </div>
                <div class="text-end">
                    <div class="small text-muted text-decoration-line-through">${peso(row.original_rate)}</div>
                    <div class="fw-semibold text-success">${peso(row.promo_rate)}</div>
                    <div class="small">Save ${peso(row.savings)}</div>
                </div>
            </div>
        `).join('');
    };

    const refreshPreview = async () => {
        const percent = Number(percentInput?.value || 0);
        if (!percent || percent < 1) {
            renderPreview([]);
            return;
        }

        const payload = {
            discount_percent: percent,
            applies_to_all: allToggle?.checked ? 1 : 0,
            accommodation_ids: Array.from(list?.querySelectorAll('.rp-promo-accommodation-check:checked') || []).map((el) => el.value),
        };

        try {
            const response = await fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            renderPreview(data.preview || []);
        } catch (e) {
            renderPreview([]);
        }
    };

    const schedulePreview = () => {
        clearTimeout(timer);
        timer = setTimeout(refreshPreview, 200);
    };

    allToggle?.addEventListener('change', () => {
        syncAllToggle();
        schedulePreview();
    });
    list?.addEventListener('change', schedulePreview);
    percentInput?.addEventListener('input', schedulePreview);

    regenerateBtn?.addEventListener('click', () => {
        const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let code = '';
        for (let i = 0; i < 8; i++) code += alphabet[Math.floor(Math.random() * alphabet.length)];
        if (codeInput) codeInput.value = code;
    });

    syncAllToggle();
    schedulePreview();
})();
</script>
@endpush
