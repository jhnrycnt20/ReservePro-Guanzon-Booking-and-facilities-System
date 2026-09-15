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
    <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#accommodationCreateModal">Add Accommodation</button>
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
                    @php
                        $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : $item->status;
                        $amenityIds = $item->amenities->pluck('id')->values()->all();
                    @endphp
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->number }}</td>
                        <td>{{ $item->type->name ?? '—' }}</td>
                        <td>{{ $item->capacity }}</td>
                        <td>₱{{ number_format($item->rate, 2) }}</td>
                        <td><x-status-badge :status="$item->status" /></td>
                        <td class="text-nowrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#accommodationViewModal"
                                data-name="{{ $item->name }}"
                                data-number="{{ $item->number }}"
                                data-type="{{ $item->type->name ?? '—' }}"
                                data-capacity="{{ $item->capacity }}"
                                data-rate="{{ number_format((float) $item->rate, 2) }}"
                                data-status="{{ ucfirst(str_replace('_', ' ', $statusValue)) }}"
                                data-description="{{ $item->description }}"
                                data-is-active="{{ $item->is_active ? 'Yes' : 'No' }}"
                                data-amenities="{{ $item->amenities->pluck('name')->implode(', ') }}"
                                data-image-url="{{ $item->image_url }}"
                            >View</button>
                            <button
                                type="button"
                                class="btn btn-sm btn-rp-soft"
                                data-bs-toggle="modal"
                                data-bs-target="#accommodationEditModal"
                                data-action="{{ route('admin.accommodations.update', $item) }}"
                                data-name="{{ $item->name }}"
                                data-number="{{ $item->number }}"
                                data-type-id="{{ $item->accommodation_type_id }}"
                                data-capacity="{{ $item->capacity }}"
                                data-rate="{{ $item->rate }}"
                                data-status="{{ $statusValue }}"
                                data-description="{{ $item->description }}"
                                data-is-active="{{ $item->is_active ? '1' : '0' }}"
                                data-amenities="{{ implode(',', $amenityIds) }}"
                                data-image-url="{{ $item->image_url }}"
                            >Edit</button>
                            <form method="POST" action="{{ route('admin.accommodations.destroy', $item) }}" class="d-inline" data-rp-confirm="Delete this accommodation?">
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

<div class="modal fade" id="accommodationViewModal" tabindex="-1" aria-labelledby="accommodationViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="accommodationViewModalLabel">Accommodation details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <img id="viewImage" src="" alt="Accommodation" class="rp-accommodation-view-img w-100">
                    </div>
                    <div class="col-md-7">
                        <h3 class="h4 mb-1" id="viewName">—</h3>
                        <p class="text-muted mb-3" id="viewNumber">—</p>
                        <div class="rp-accommodation-view-grid">
                            <div><span class="text-muted">Type</span><strong id="viewType">—</strong></div>
                            <div><span class="text-muted">Capacity</span><strong id="viewCapacity">—</strong></div>
                            <div><span class="text-muted">Rate</span><strong id="viewRate">—</strong></div>
                            <div><span class="text-muted">Status</span><strong id="viewStatus">—</strong></div>
                            <div><span class="text-muted">Active listing</span><strong id="viewIsActive">—</strong></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small mb-1">Description</div>
                        <p class="mb-0" id="viewDescription">—</p>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small mb-1">Amenities</div>
                        <p class="mb-0" id="viewAmenities">—</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="accommodationCreateModal" tabindex="-1" aria-labelledby="accommodationCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.accommodations.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5" id="accommodationCreateModalLabel">Add Accommodation</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number / Code</label>
                            <input type="text" name="number" class="form-control" value="{{ old('number') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="accommodation_type_id" class="form-select" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', 2) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate</label>
                            <input type="number" step="0.01" min="0" name="rate" class="form-control" value="{{ old('rate') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach(['available','reserved','occupied','maintenance','inactive'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', 'available') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amenities</label>
                            <div class="row g-2">
                                @forelse($amenities as $amenity)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="createAmenity{{ $amenity->id }}" @checked(collect(old('amenities', []))->contains($amenity->id))>
                                            <label class="form-check-label" for="createAmenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted small">No amenities yet. Add some under Amenities first.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12 form-check ms-1">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" @checked(old('is_active', true))>
                            <label class="form-check-label" for="createIsActive">Active listing</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-rp-primary">Create Accommodation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="accommodationEditModal" tabindex="-1" aria-labelledby="accommodationEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="accommodationEditForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h2 class="modal-title h5" id="accommodationEditModalLabel">Edit Accommodation</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number / Code</label>
                            <input type="text" name="number" id="editNumber" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="accommodation_type_id" id="editTypeId" class="form-select" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="capacity" id="editCapacity" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate</label>
                            <input type="number" step="0.01" min="0" name="rate" id="editRate" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select" required>
                                @foreach(['available','reserved','occupied','maintenance','inactive'] as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="editImagePreviewWrap" class="mt-2 d-none">
                                <img id="editImagePreview" src="" alt="Current image" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amenities</label>
                            <div class="row g-2">
                                @forelse($amenities as $amenity)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input edit-amenity" type="checkbox" name="amenities[]" value="{{ $amenity->id }}" id="editAmenity{{ $amenity->id }}">
                                            <label class="form-check-label" for="editAmenity{{ $amenity->id }}">{{ $amenity->name }}</label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted small">No amenities yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12 form-check ms-1">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                            <label class="form-check-label" for="editIsActive">Active listing</label>
                        </div>
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
document.getElementById('accommodationViewModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    if (!button) return;

    document.getElementById('viewName').textContent = button.dataset.name || '—';
    document.getElementById('viewNumber').textContent = button.dataset.number ? `No. ${button.dataset.number}` : '—';
    document.getElementById('viewType').textContent = button.dataset.type || '—';
    document.getElementById('viewCapacity').textContent = button.dataset.capacity || '—';
    document.getElementById('viewRate').textContent = button.dataset.rate ? `₱${button.dataset.rate}` : '—';
    document.getElementById('viewStatus').textContent = button.dataset.status || '—';
    document.getElementById('viewIsActive').textContent = button.dataset.isActive || '—';
    document.getElementById('viewDescription').textContent = button.dataset.description || 'No description.';
    document.getElementById('viewAmenities').textContent = button.dataset.amenities || 'None';

    const image = document.getElementById('viewImage');
    image.src = button.dataset.imageUrl || '';
    image.alt = button.dataset.name || 'Accommodation';
});

document.getElementById('accommodationEditModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    const form = document.getElementById('accommodationEditForm');
    if (!button || !form) return;

    form.action = button.dataset.action || '';
    document.getElementById('editName').value = button.dataset.name || '';
    document.getElementById('editNumber').value = button.dataset.number || '';
    document.getElementById('editTypeId').value = button.dataset.typeId || '';
    document.getElementById('editCapacity').value = button.dataset.capacity || '';
    document.getElementById('editRate').value = button.dataset.rate || '';
    document.getElementById('editStatus').value = button.dataset.status || 'available';
    document.getElementById('editDescription').value = button.dataset.description || '';
    document.getElementById('editIsActive').checked = button.dataset.isActive === '1';

    const selectedAmenities = new Set(
        (button.dataset.amenities || '')
            .split(',')
            .map((id) => id.trim())
            .filter(Boolean)
    );
    document.querySelectorAll('.edit-amenity').forEach((checkbox) => {
        checkbox.checked = selectedAmenities.has(checkbox.value);
    });

    const previewWrap = document.getElementById('editImagePreviewWrap');
    const preview = document.getElementById('editImagePreview');
    const imageUrl = button.dataset.imageUrl || '';
    if (imageUrl) {
        preview.src = imageUrl;
        previewWrap.classList.remove('d-none');
    } else {
        preview.removeAttribute('src');
        previewWrap.classList.add('d-none');
    }
});

@if($errors->any() && !old('_method'))
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('accommodationCreateModal');
    if (modalEl && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});
@endif
</script>
@endpush
