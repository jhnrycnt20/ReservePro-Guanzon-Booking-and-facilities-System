@extends('layouts.dashboard')

@section('title', 'Promo '.$promo->code)
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Promo '.$promo->code)
@section('page_subtitle', $promo->name ?: 'Promo details and discounted rates')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="rp-card">
            <div class="text-muted small">Code</div>
            <div class="fs-4 fw-semibold mb-3"><code>{{ $promo->code }}</code></div>
            <div class="text-muted small">Discount</div>
            <div class="mb-3">{{ rtrim(rtrim(number_format((float) $promo->discount_percent, 2), '0'), '.') }}% off</div>
            <div class="text-muted small">Scope</div>
            <div class="mb-3">{{ $promo->applies_to_all ? 'All accommodations' : $promo->accommodations->count().' selected' }}</div>
            <div class="text-muted small">Usage</div>
            <div class="mb-3">
                {{ $promo->used_count }}
                @if($promo->usage_limit) / {{ $promo->usage_limit }} @endif
            </div>
            <div class="text-muted small">Status</div>
            <div class="mb-3">
                @if($promo->is_active)
                    <span class="badge text-bg-success">Active</span>
                @else
                    <span class="badge text-bg-secondary">Inactive</span>
                @endif
            </div>
            <a href="{{ route('admin.promos.edit', $promo) }}" class="btn btn-rp-primary w-100 mb-2">Edit percentage</a>
            <a href="{{ route('admin.promos.index') }}" class="btn btn-rp-soft w-100">Back to list</a>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="rp-card">
            <h2 class="h5 mb-3">Promo price preview</h2>
            <div class="rp-promo-preview">
                @forelse($preview as $row)
                    <div class="rp-promo-preview-row">
                        <div>
                            <div class="fw-semibold">{{ $row['name'] }}</div>
                            <div class="small text-muted">{{ $row['number'] }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted text-decoration-line-through">₱{{ number_format($row['original_rate'], 2) }}</div>
                            <div class="fw-semibold text-success">₱{{ number_format($row['promo_rate'], 2) }}</div>
                            <div class="small">Save ₱{{ number_format($row['savings'], 2) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">No accommodations linked.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
