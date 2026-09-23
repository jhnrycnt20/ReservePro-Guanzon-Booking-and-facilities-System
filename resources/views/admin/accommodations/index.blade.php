@extends('layouts.dashboard')

@section('title', 'Accommodations')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Accommodations')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <a href="{{ route('admin.accommodations.create') }}" class="btn btn-rp-primary">Add Accommodation</a>
    </div>
    @include('partials.list-filters', [
        'filters' => [
            [
                'name' => 'type',
                'label' => 'Type',
                'empty' => 'All types',
                'value' => request('type'),
                'options' => $types->pluck('name', 'id')->all(),
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'empty' => 'All statuses',
                'value' => request('status'),
                'options' => [
                    'available' => 'Available',
                    'reserved' => 'Reserved',
                    'occupied' => 'Occupied',
                    'maintenance' => 'Maintenance',
                    'inactive' => 'Inactive',
                ],
            ],
            [
                'name' => 'active',
                'label' => 'Listed',
                'empty' => 'All',
                'value' => request('active'),
                'options' => [
                    '1' => 'Active',
                    '0' => 'Hidden',
                ],
            ],
        ],
        'searchPlaceholder' => 'Name, Number, or Description',
        'clearUrl' => route('admin.accommodations.index'),
        'embedded' => true,
    ])
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 18%;">Name</th>
                    <th style="width: 14%;">Number</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 10%;">Capacity</th>
                    <th style="width: 14%;">Rate</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 22%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($accommodations as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->number }}</td>
                        <td>{{ $item->type->name ?? '—' }}</td>
                        <td>{{ $item->capacity }}</td>
                        <td>₱{{ number_format($item->rate, 2) }}</td>
                        <td><x-status-badge :status="$item->status" plain /></td>
                        <td class="text-nowrap">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.accommodations.show', $item) }}" class="btn btn-sm btn-rp-soft">View</a>
                                <a href="{{ route('admin.accommodations.edit', $item) }}" class="btn btn-sm btn-rp-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.accommodations.destroy', $item) }}" data-rp-confirm="Delete this accommodation?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No accommodations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($accommodations, 'links')) {{ $accommodations->withQueryString()->links() }} @endif
</div>
@endsection
