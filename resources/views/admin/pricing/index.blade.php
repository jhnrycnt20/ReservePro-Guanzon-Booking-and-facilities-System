@extends('layouts.dashboard')

@section('title', 'Pricing')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Pricing')
@section('page_subtitle', 'Seasonal and special rates')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.pricing.create') }}" class="btn btn-rp-primary">Add Pricing</a>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Accommodation</th>
                    <th>Amount</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pricing as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->accommodation->name ?? '—' }}</td>
                        <td>₱{{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->start_date ? \Illuminate\Support\Carbon::parse($item->start_date)->format('M d, Y') : '—' }}</td>
                        <td>{{ $item->end_date ? \Illuminate\Support\Carbon::parse($item->end_date)->format('M d, Y') : '—' }}</td>
                        <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.pricing.edit', $item) }}" class="btn btn-sm btn-rp-soft">Edit</a>
                            <form method="POST" action="{{ route('admin.pricing.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this pricing rule?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No pricing rules found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($pricing, 'links')) {{ $pricing->withQueryString()->links() }} @endif
</div>
@endsection
