@extends('layouts.dashboard')

@section('title', 'Accommodations')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Accommodations')
@section('page_subtitle', 'Rooms and facilities inventory')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.accommodations.create') }}" class="btn btn-rp-primary">Add Accommodation</a>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th></th>
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
                        <td><x-status-badge :status="$item->status" /></td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.accommodations.edit', $item) }}" class="btn btn-sm btn-rp-soft">Edit</a>
                            <form method="POST" action="{{ route('admin.accommodations.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this accommodation?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
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
