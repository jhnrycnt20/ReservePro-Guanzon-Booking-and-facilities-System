@extends('layouts.dashboard')

@section('title', 'View Promo Code')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'View Promo Code')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-card">
    <div class="mb-4">
        <div class="text-muted small">Promo code</div>
        <div class="fs-3 fw-bold text-body"><code class="text-body">{{ $promo->code }}</code></div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Discount</div>
                <div class="value">{{ rtrim(rtrim(number_format((float) $promo->discount_percent, 2), '0'), '.') }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Scope</div>
                <div class="value">{{ $promo->applies_to_all ? 'All' : $promo->accommodations->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="rp-stat">
                <div class="label">Usage</div>
                <div class="value">{{ $promo->used_count }}{{ $promo->usage_limit ? ' / '.$promo->usage_limit : '' }}</div>
            </div>
        </div>
    </div>

    <hr class="my-4">

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

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('admin.promos.index') }}" class="btn btn-rp-soft">Back</a>
        <a href="{{ route('admin.promos.edit', $promo) }}" class="btn btn-rp-primary">Edit percentage</a>
    </div>
</div>
@endsection
