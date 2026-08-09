@extends('layouts.dashboard')

@section('title', 'Amenities')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Amenities')
@section('page_subtitle', 'Features available across accommodations')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.amenities.create') }}" class="btn btn-rp-primary">Add Amenity</a>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Icon</th>
                    <th>Description</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($amenities as $amenity)
                    <tr>
                        <td>{{ $amenity->name }}</td>
                        <td>{{ $amenity->icon ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($amenity->description, 80) ?: '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.amenities.edit', $amenity) }}" class="btn btn-sm btn-rp-soft">Edit</a>
                            <form method="POST" action="{{ route('admin.amenities.destroy', $amenity) }}" class="d-inline" onsubmit="return confirm('Delete this amenity?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No amenities found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($amenities, 'links')) {{ $amenities->withQueryString()->links() }} @endif
</div>
@endsection
