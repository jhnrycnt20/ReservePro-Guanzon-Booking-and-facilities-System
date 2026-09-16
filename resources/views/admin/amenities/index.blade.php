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
<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#amenityCreateModal">Add Amenity</button>
    </div>
    @include('partials.list-filters', [
        'searchPlaceholder' => 'Amenity name or description',
        'clearUrl' => route('admin.amenities.index'),
        'embedded' => true,
    ])
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
                            <button
                                type="button"
                                class="btn btn-sm btn-rp-soft"
                                data-bs-toggle="modal"
                                data-bs-target="#amenityEditModal"
                                data-action="{{ route('admin.amenities.update', $amenity) }}"
                                data-name="{{ $amenity->name }}"
                                data-icon="{{ $amenity->icon }}"
                                data-description="{{ $amenity->description }}"
                            >Edit</button>
                            <form method="POST" action="{{ route('admin.amenities.destroy', $amenity) }}" class="d-inline" data-rp-confirm="Delete this amenity?">
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

<div class="modal fade" id="amenityCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.amenities.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5">Add Amenity</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. bi-wifi">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-rp-primary">Create Amenity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="amenityEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="amenityEditForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title h5">Edit Amenity</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="amenityEditName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" id="amenityEditIcon" class="form-control" placeholder="e.g. bi-wifi">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="amenityEditDescription" class="form-control" rows="3"></textarea>
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
document.getElementById('amenityEditModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    const form = document.getElementById('amenityEditForm');
    if (!button || !form) return;
    form.action = button.dataset.action || '';
    document.getElementById('amenityEditName').value = button.dataset.name || '';
    document.getElementById('amenityEditIcon').value = button.dataset.icon || '';
    document.getElementById('amenityEditDescription').value = button.dataset.description || '';
});
</script>
@endpush
