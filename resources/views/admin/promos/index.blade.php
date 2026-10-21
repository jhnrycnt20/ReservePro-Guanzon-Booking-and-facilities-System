@extends('layouts.dashboard')

@section('title', 'Promos')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Promo Codes')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" data-rp-auto-dismiss="2000">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#rpPromoCreateModal">Create Promo</button>
    </div>
    @include('partials.list-filters', [
        'filters' => [
            [
                'name' => 'active',
                'label' => 'Active',
                'empty' => 'All',
                'value' => request('active'),
                'options' => [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ],
            ],
        ],
        'searchPlaceholder' => 'Code or name',
        'clearUrl' => route('admin.promos.index'),
        'embedded' => true,
    ])
</div>

<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 12%;">Code</th>
                    <th style="width: 26%;">Name</th>
                    <th style="width: 10%;">Discount</th>
                    <th style="width: 20%;">Scope</th>
                    <th style="width: 8%;">Used</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 14%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($promos as $promo)
                    <tr>
                        <td><code class="fw-semibold text-body">{{ $promo->code }}</code></td>
                        <td>{{ $promo->name ?: '—' }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $promo->discount_percent, 2), '0'), '.') }}%</td>
                        <td>
                            @if($promo->applies_to_all)
                                All accommodations
                            @else
                                <div class="small">{{ $promo->accommodations->pluck('name')->filter()->join(', ') ?: '—' }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $promo->used_count }}
                            @if($promo->usage_limit) / {{ $promo->usage_limit }} @endif
                        </td>
                        <td>{{ $promo->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-nowrap">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpPromoViewModal{{ $promo->id }}">View</button>
                                <button type="button" class="btn btn-sm rp-staff-edit-button" data-bs-toggle="modal" data-bs-target="#rpPromoEditModal{{ $promo->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" data-rp-confirm="Delete this promo?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No promo codes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($promos, 'links')) {{ $promos->withQueryString()->links() }} @endif
</div>

{{-- Create Promo --}}
<div class="modal fade" id="rpPromoCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content rp-admin-modal" method="POST" action="{{ route('admin.promos.store') }}" id="rpPromoCreateForm" data-preview-url="{{ route('admin.promos.preview') }}" data-rp-promo-form>
                @csrf
                <input type="hidden" name="_modal" value="promo_create">
                <div class="modal-header">
                    <h2 class="modal-title h5">Create Promo</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="rp-promo-form-stack">
                        <div>
                            <label class="form-label">Accommodations</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="applies_to_all" value="1" id="createAppliesToAll" data-rp-promo-all @checked(old('_modal') === 'promo_create' && old('applies_to_all'))>
                                <label class="form-check-label" for="createAppliesToAll">Apply to all accommodations</label>
                            </div>
                            <div class="dropdown rp-promo-acc-dropdown" data-rp-promo-acc-wrap>
                                <button class="form-select text-start rp-promo-acc-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
                                    <span data-rp-promo-acc-label>Select accommodations</span>
                                </button>
                                <div class="dropdown-menu p-2" data-rp-promo-list>
                                    @foreach($accommodations as $acc)
                                        <label class="dropdown-item rp-promo-acc-option">
                                            <input type="checkbox" name="accommodation_ids[]" value="{{ $acc->id }}" class="form-check-input rp-promo-accommodation-check me-2" data-rate="{{ $acc->rate }}" data-name="{{ $acc->name }}" @checked(old('_modal') === 'promo_create' && collect(old('accommodation_ids', []))->contains($acc->id))>
                                            <span>{{ $acc->name }} <span class="text-muted">({{ $acc->number }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Promo name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('_modal') === 'promo_create' ? old('name') : '' }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control text-uppercase" value="{{ old('_modal') === 'promo_create' ? old('code', $generatedCode) : $generatedCode }}" maxlength="32" required data-rp-promo-code>
                        </div>
                        <div>
                            <label class="form-label">Discount %</label>
                            <input type="number" step="0.01" min="1" max="100" name="discount_percent" class="form-control" value="{{ old('_modal') === 'promo_create' ? old('discount_percent', 10) : 10 }}" required data-rp-promo-percent>
                        </div>
                        <div>
                            <label class="form-label">Usage limit</label>
                            <input type="number" min="1" name="usage_limit" class="form-control" value="{{ old('_modal') === 'promo_create' ? old('usage_limit') : '' }}" placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Starts</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('_modal') === 'promo_create' ? old('starts_at') : '' }}">
                        </div>
                        <div>
                            <label class="form-label">Ends</label>
                            <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('_modal') === 'promo_create' ? old('ends_at') : '' }}">
                        </div>
                        <div class="rp-promo-preview" data-rp-promo-preview>
                            <div class="text-muted small">Select accommodations and enter a percentage to preview prices.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rp-primary">Save Promo</button>
                </div>
        </form>
    </div>
</div>

@foreach($promos as $promo)
    @php
        $selectedIds = collect(old('_modal') === 'promo_edit_'.$promo->id ? old('accommodation_ids', []) : $promo->accommodations->pluck('id')->all());
        $startsAt = old('_modal') === 'promo_edit_'.$promo->id ? old('starts_at') : optional($promo->starts_at)->format('Y-m-d\TH:i');
        $endsAt = old('_modal') === 'promo_edit_'.$promo->id ? old('ends_at') : optional($promo->ends_at)->format('Y-m-d\TH:i');
    @endphp

    <div class="modal fade" id="rpPromoViewModal{{ $promo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rp-admin-modal">
                <div class="modal-header">
                    <h2 class="modal-title h5">Promo {{ $promo->code }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-4"><div class="rp-stat"><div class="label">Discount</div><div class="value">{{ rtrim(rtrim(number_format((float) $promo->discount_percent, 2), '0'), '.') }}%</div></div></div>
                        <div class="col-4"><div class="rp-stat"><div class="label">Scope</div><div class="value">{{ $promo->applies_to_all ? 'All' : $promo->accommodations->count() }}</div></div></div>
                        <div class="col-4"><div class="rp-stat"><div class="label">Usage</div><div class="value">{{ $promo->used_count }}{{ $promo->usage_limit ? ' / '.$promo->usage_limit : '' }}</div></div></div>
                    </div>
                    <p class="mb-1"><strong>Name:</strong> {{ $promo->name ?: '—' }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $promo->is_active ? 'Active' : 'Inactive' }}</p>
                    <p class="mb-1"><strong>Starts:</strong> {{ $promo->starts_at?->format('M d, Y g:i A') ?? '—' }}</p>
                    <p class="mb-3"><strong>Ends:</strong> {{ $promo->ends_at?->format('M d, Y g:i A') ?? '—' }}</p>
                    @unless($promo->applies_to_all)
                        <h3 class="h6">Accommodations</h3>
                        <p class="text-muted">{{ $promo->accommodations->pluck('name')->join(', ') ?: '—' }}</p>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rpPromoEditModal{{ $promo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form class="modal-content rp-admin-modal" method="POST" action="{{ route('admin.promos.update', $promo) }}" data-preview-url="{{ route('admin.promos.preview') }}" data-rp-promo-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="promo_edit_{{ $promo->id }}">
                    <div class="modal-header">
                        <h2 class="modal-title h5">Edit Promo</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rp-promo-form-stack">
                            <div>
                                <label class="form-label">Accommodations</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="applies_to_all" value="1" id="editAppliesToAll{{ $promo->id }}" data-rp-promo-all @checked(old('_modal') === 'promo_edit_'.$promo->id ? old('applies_to_all') : $promo->applies_to_all)>
                                    <label class="form-check-label" for="editAppliesToAll{{ $promo->id }}">Apply to all accommodations</label>
                                </div>
                                <div class="dropdown rp-promo-acc-dropdown" data-rp-promo-acc-wrap>
                                    <button class="form-select text-start rp-promo-acc-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
                                        <span data-rp-promo-acc-label>Select accommodations</span>
                                    </button>
                                    <div class="dropdown-menu p-2" data-rp-promo-list>
                                        @foreach($accommodations as $acc)
                                            <label class="dropdown-item rp-promo-acc-option">
                                                <input type="checkbox" name="accommodation_ids[]" value="{{ $acc->id }}" class="form-check-input rp-promo-accommodation-check me-2" data-rate="{{ $acc->rate }}" data-name="{{ $acc->name }}" @checked($selectedIds->contains($acc->id))>
                                                <span>{{ $acc->name }} <span class="text-muted">({{ $acc->number }})</span></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Promo name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('_modal') === 'promo_edit_'.$promo->id ? old('name') : $promo->name }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control text-uppercase" value="{{ old('_modal') === 'promo_edit_'.$promo->id ? old('code') : $promo->code }}" maxlength="32" required data-rp-promo-code>
                            </div>
                            <div>
                                <label class="form-label">Discount %</label>
                                <input type="number" step="0.01" min="1" max="100" name="discount_percent" class="form-control" value="{{ old('_modal') === 'promo_edit_'.$promo->id ? old('discount_percent') : $promo->discount_percent }}" required data-rp-promo-percent>
                            </div>
                            <div>
                                <label class="form-label">Usage limit</label>
                                <input type="number" min="1" name="usage_limit" class="form-control" value="{{ old('_modal') === 'promo_edit_'.$promo->id ? old('usage_limit') : $promo->usage_limit }}" placeholder="Unlimited">
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                @php $activeVal = old('_modal') === 'promo_edit_'.$promo->id ? old('is_active') : ($promo->is_active ? '1' : '0'); @endphp
                                <select name="is_active" class="form-select">
                                    <option value="1" @selected($activeVal == '1')>Active</option>
                                    <option value="0" @selected($activeVal == '0')>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Starts</label>
                                <input type="datetime-local" name="starts_at" class="form-control" value="{{ $startsAt }}">
                            </div>
                            <div>
                                <label class="form-label">Ends</label>
                                <input type="datetime-local" name="ends_at" class="form-control" value="{{ $endsAt }}">
                            </div>
                            <div class="rp-promo-preview" data-rp-promo-preview>
                                <div class="text-muted small">Preview updates as you change the percentage.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rp-primary">Save Changes</button>
                    </div>
            </form>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalKey = @json(old('_modal'));
    if (modalKey) {
        let targetId = null;
        if (modalKey === 'promo_create') targetId = 'rpPromoCreateModal';
        else if (modalKey.startsWith('promo_edit_')) targetId = `rpPromoEditModal${modalKey.replace('promo_edit_', '')}`;
        const el = targetId ? document.getElementById(targetId) : null;
        if (el && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(el).show();
    }

    const peso = (n) => `₱${Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    document.querySelectorAll('[data-rp-promo-form]').forEach((form) => {
        const previewUrl = form.dataset.previewUrl;
        const previewEl = form.querySelector('[data-rp-promo-preview]');
        const allToggle = form.querySelector('[data-rp-promo-all]');
        const list = form.querySelector('[data-rp-promo-list]');
        const wrap = form.querySelector('[data-rp-promo-acc-wrap]');
        const labelEl = form.querySelector('[data-rp-promo-acc-label]');
        const trigger = form.querySelector('.rp-promo-acc-trigger');
        const percentInput = form.querySelector('[data-rp-promo-percent]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        let timer = null;

        const selectedChecks = () => Array.from(list?.querySelectorAll('.rp-promo-accommodation-check:checked') || []);

        const syncLabel = () => {
            if (!labelEl) return;
            if (allToggle?.checked) {
                labelEl.textContent = 'All accommodations';
                return;
            }
            const selected = selectedChecks();
            if (!selected.length) {
                labelEl.textContent = 'Select accommodations';
                return;
            }
            if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.name || '1 selected';
                return;
            }
            labelEl.textContent = `${selected.length} accommodations selected`;
        };

        const syncAll = () => {
            const disabled = Boolean(allToggle?.checked);
            wrap?.classList.toggle('is-disabled', disabled);
            if (trigger) trigger.disabled = disabled;
            list?.querySelectorAll('.rp-promo-accommodation-check').forEach((el) => { el.disabled = disabled; });
            syncLabel();
        };

        const renderPreview = (rows) => {
            if (!previewEl) return;
            if (!rows.length) {
                previewEl.innerHTML = '<div class="text-muted small">Select accommodations and enter a percentage to preview prices.</div>';
                return;
            }
            const shown = rows.slice(0, 2);
            const extra = rows.length - shown.length;
            previewEl.innerHTML = shown.map((row) => `
                <div class="rp-promo-preview-row">
                    <div class="fw-semibold text-truncate">${row.name}</div>
                    <div class="text-end text-nowrap">
                        <span class="small text-muted text-decoration-line-through me-1">${peso(row.original_rate)}</span>
                        <span class="fw-semibold text-success">${peso(row.promo_rate)}</span>
                    </div>
                </div>
            `).join('') + (extra > 0 ? `<div class="text-muted small">+${extra} more</div>` : '');
        };

        const refresh = async () => {
            const percent = Number(percentInput?.value || 0);
            if (!percent || percent < 1) { renderPreview([]); return; }
            try {
                const response = await fetch(previewUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
                    body: JSON.stringify({
                        discount_percent: percent,
                        applies_to_all: allToggle?.checked ? 1 : 0,
                        accommodation_ids: selectedChecks().map((el) => el.value),
                    }),
                });
                const data = await response.json();
                renderPreview(data.preview || []);
            } catch (e) { renderPreview([]); }
        };

        const schedule = () => { clearTimeout(timer); timer = setTimeout(refresh, 200); };
        allToggle?.addEventListener('change', () => { syncAll(); schedule(); });
        list?.addEventListener('change', () => { syncLabel(); schedule(); });
        percentInput?.addEventListener('input', schedule);
        syncAll();
        schedule();
    });
});
</script>
@endpush
