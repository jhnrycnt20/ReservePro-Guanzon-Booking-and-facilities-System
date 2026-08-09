@extends('layouts.dashboard')

@section('title', 'Accommodation Types')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Accommodation Types')
@section('page_subtitle', 'Categories for rooms and facilities')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.types.create') }}" class="btn btn-rp-primary">Add Type</a>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->slug }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($type->description, 80) ?: '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.types.edit', $type) }}" class="btn btn-sm btn-rp-soft">Edit</a>
                            <form method="POST" action="{{ route('admin.types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('Delete this type?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No types found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($types, 'links')) {{ $types->withQueryString()->links() }} @endif
</div>
@endsection
