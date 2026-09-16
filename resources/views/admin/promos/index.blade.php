@extends('layouts.dashboard')

@section('title', 'Promos')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Promo Codes')
@section('page_subtitle', 'Create discount codes for accommodations')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <a href="{{ route('admin.promos.create') }}" class="btn btn-rp-primary">Create Promo</a>
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
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Discount</th>
                    <th>Scope</th>
                    <th>Used</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($promos as $promo)
                    <tr>
                        <td><code class="fw-semibold">{{ $promo->code }}</code></td>
                        <td>{{ $promo->name ?: '—' }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $promo->discount_percent, 2), '0'), '.') }}%</td>
                        <td>
                            @if($promo->applies_to_all)
                                All accommodations
                            @else
                                <div class="small">
                                    {{ $promo->accommodations->pluck('name')->filter()->join(', ') ?: '—' }}
                                </div>
                            @endif
                        </td>
                        <td>
                            {{ $promo->used_count }}
                            @if($promo->usage_limit)
                                / {{ $promo->usage_limit }}
                            @endif
                        </td>
                        <td>
                            @if($promo->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.promos.show', $promo) }}" class="btn btn-sm btn-rp-soft">View</a>
                            <a href="{{ route('admin.promos.edit', $promo) }}" class="btn btn-sm btn-rp-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.promos.toggle', $promo) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $promo->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" class="d-inline" data-rp-confirm="Delete this promo?">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
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
@endsection
