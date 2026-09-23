@extends('layouts.dashboard')

@section('title', 'Accommodation Types')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Accommodation Types')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#typeCreateModal">Add Type</button>
    </div>
    @include('partials.list-filters', [
        'searchPlaceholder' => 'Type name or description',
        'clearUrl' => route('admin.types.index'),
        'embedded' => true,
    ])
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
                            <div class="d-flex justify-content-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-rp-soft"
                                    data-bs-toggle="modal"
                                    data-bs-target="#typeEditModal"
                                    data-action="{{ route('admin.types.update', $type) }}"
                                    data-name="{{ $type->name }}"
                                    data-description="{{ $type->description }}"
                                >Edit</button>
                                <form method="POST" action="{{ route('admin.types.destroy', $type) }}" data-rp-confirm="Delete this type?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
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

<div class="modal fade" id="typeCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.types.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5">Add Type</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-rp-primary">Create Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="typeEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="typeEditForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title h5">Edit Type</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="typeEditName" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="typeEditDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-rp-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('typeEditModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    const form = document.getElementById('typeEditForm');
    if (!button || !form) return;
    form.action = button.dataset.action || '';
    document.getElementById('typeEditName').value = button.dataset.name || '';
    document.getElementById('typeEditDescription').value = button.dataset.description || '';
});
</script>
@endpush
