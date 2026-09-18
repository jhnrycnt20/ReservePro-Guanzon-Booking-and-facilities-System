@extends('layouts.dashboard')

@section('title', 'Edit Promo')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Edit Promo '.$promo->code)
@section('page_subtitle', 'Update discount percentage, rooms, and promo details')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@php
    $selectedIds = collect(old('accommodation_ids', $promo->accommodations->pluck('id')->all()));
    $startsAt = old('starts_at', optional($promo->starts_at)->format('Y-m-d\\TH:i'));
    $endsAt = old('ends_at', optional($promo->ends_at)->format('Y-m-d\\TH:i'));
    $nowLocal = now()->format('Y-m-d\\TH:i');
@endphp
<form method="POST" action="{{ route('admin.promos.update', $promo) }}" id="rpPromoForm" data-preview-url="{{ route('admin.promos.preview') }}">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="rp-card mb-3">
                <h2 class="h5 mb-3">1. Choose accommodations</h2>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="applies_to_all" value="1" id="appliesToAll" @checked(old('applies_to_all', $promo->applies_to_all))>
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
                                @checked($selectedIds->contains($item->id))
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
                        <input type="text" name="name" id="promoName" class="form-control" value="{{ old('name', $promo->name) }}" placeholder="Summer stay discount">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="promoCode">Promo code</label>
                        <input type="text" name="code" id="promoCode" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $promo->code) }}" maxlength="32" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-rp-soft w-100" id="rpGeneratePromoCode">Regenerate</button>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="startsAtDisplay">Starts (optional)</label>
                        <div class="rp-promo-dt-field" data-rp-promo-dt-field data-rp-promo-dt-role="start" data-rp-allow-past-start="{{ ($startsAt && $startsAt < $nowLocal) ? '1' : '0' }}">
                            <input type="hidden" name="starts_at" id="startsAt" value="{{ $startsAt }}" data-rp-promo-dt-value>
                            <button type="button" class="form-control text-start rp-promo-dt-trigger" id="startsAtDisplay" data-rp-promo-dt-open>
                                <span data-rp-promo-dt-label>{{ $startsAt ? \Carbon\Carbon::parse($startsAt)->format('m/d/Y g:i A') : 'Select start date & time' }}</span>
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="form-text">Past dates and times cannot be selected for new start times.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="endsAtDisplay">Ends (optional)</label>
                        <div class="rp-promo-dt-field" data-rp-promo-dt-field data-rp-promo-dt-role="end">
                            <input type="hidden" name="ends_at" id="endsAt" value="{{ $endsAt }}" data-rp-promo-dt-value>
                            <button type="button" class="form-control text-start rp-promo-dt-trigger" id="endsAtDisplay" data-rp-promo-dt-open>
                                <span data-rp-promo-dt-label>{{ $endsAt ? \Carbon\Carbon::parse($endsAt)->format('m/d/Y g:i A') : 'Select end date & time' }}</span>
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="usageLimit">Usage limit (optional)</label>
                        <input type="number" min="1" name="usage_limit" id="usageLimit" class="form-control" value="{{ old('usage_limit', $promo->usage_limit) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $promo->is_active))>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="rp-card rp-sticky-under-topbar">
                <h2 class="h5 mb-3">3. Promo percentage & price preview</h2>
                <div class="mb-3">
                    <label class="form-label" for="discountPercent">Promo percentage</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="1" max="100" name="discount_percent" id="discountPercent" class="form-control @error('discount_percent') is-invalid @enderror" value="{{ old('discount_percent', $promo->discount_percent) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    @error('discount_percent')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">Change the percentage to refresh discounted rates.</div>
                </div>
                <div id="rpPromoPreview" class="rp-promo-preview">
                    <div class="text-muted small">Updating preview…</div>
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 mt-3">Save changes</button>
                <a href="{{ route('admin.promos.show', $promo) }}" class="btn btn-rp-soft w-100 mt-2">Cancel</a>
            </div>
        </div>
    </div>
</form>

@include('partials.promo-datetime-modal')
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
