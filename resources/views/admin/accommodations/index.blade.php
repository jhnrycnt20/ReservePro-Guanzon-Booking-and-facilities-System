@extends('layouts.dashboard')

@section('title', 'Accommodations')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Accommodations')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
@endif

<div class="rp-filter-with-action mb-3">
    <div class="rp-filter-with-action__btn">
        <button type="button" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#rpAccCreateModal">Add Accommodation</button>
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
<div class="rp-card rp-admin-list-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--accommodations">
            <thead>
                <tr>
                    <th scope="col" style="width: 17%;">Name</th>
                    <th scope="col" style="width: 14%;">Number</th>
                    <th scope="col" style="width: 12%;">Type</th>
                    <th scope="col" style="width: 10%;">Capacity</th>
                    <th scope="col" style="width: 14%;">Rate</th>
                    <th scope="col" style="width: 11%;">Status</th>
                    <th scope="col" class="rp-admin-list-actions-heading" style="width: 22%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accommodations as $item)
                    <tr class="rp-clickable-row" role="button" tabindex="0" data-rp-row-modal="#rpAccViewModal{{ $item->id }}" title="View accommodation">
                        <td class="rp-admin-list-primary">
                            <span class="rp-clickable-row-label">{{ $item->name }}</span>
                        </td>
                        <td class="rp-admin-list-mono">{{ $item->number }}</td>
                        <td>{{ $item->type->name ?? '—' }}</td>
                        <td>{{ $item->capacity }}</td>
                        <td class="rp-admin-list-mono">₱{{ number_format($item->rate, 2) }}</td>
                        <td><x-status-badge :status="$item->status" /></td>
                        <td class="text-nowrap" data-rp-stop-row-click>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-rp-soft" data-bs-toggle="modal" data-bs-target="#rpAccViewModal{{ $item->id }}">View</button>
                                <button type="button" class="btn btn-sm rp-staff-edit-button" data-bs-toggle="modal" data-bs-target="#rpAccEditModal{{ $item->id }}">Edit</button>
                                <form method="POST" action="{{ route('admin.accommodations.destroy', $item) }}" data-rp-confirm="Are you sure you want to permanently delete this accommodation? This action cannot be undone.">
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

{{-- Create --}}
@php
    $isAccCreate = old('_modal') === 'acc_create';
@endphp
<div class="modal fade" id="rpAccCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content rp-admin-modal" method="POST" action="{{ route('admin.accommodations.store') }}" enctype="multipart/form-data" data-rp-acc-form>
                @csrf
                <input type="hidden" name="_modal" value="acc_create">
                <div class="modal-header">
                    <h2 class="modal-title h5">Add Accommodation</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="rp-promo-form-stack">
                        <div>
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('_modal') === 'acc_create' ? old('name') : '' }}" placeholder="e.g. Deluxe Family Cabin" required>
                            <x-accommodation-field-error field="name" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Number</label>
                            <select name="number" class="form-select" required>
                                <option value="" disabled @selected(old('_modal') !== 'acc_create' || old('number') === null)>Select a number</option>
                                @foreach(range(1, 10) as $number)
                                    <option value="{{ $number }}" @selected(old('_modal') === 'acc_create' && (string) old('number') === (string) $number)>{{ $number }}</option>
                                @endforeach
                            </select>
                            <x-accommodation-field-error field="number" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="accommodation_type_id" class="form-select" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('_modal') === 'acc_create' && old('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <x-accommodation-field-error field="accommodation_type_id" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" max="50" name="capacity" class="form-control rp-no-spinner" value="{{ old('_modal') === 'acc_create' ? old('capacity', 2) : 2 }}" required>
                            <x-accommodation-field-error field="capacity" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Rate</label>
                            <input type="number" step="0.01" min="0" max="999999.99" name="rate" class="form-control rp-no-spinner" value="{{ old('_modal') === 'acc_create' ? old('rate') : '' }}" placeholder="e.g. 2500.00" required>
                            <x-accommodation-field-error field="rate" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Availability</label>
                            <select name="status" class="form-select" required>
                                @foreach(\App\Enums\AccommodationStatus::manualLabels() as $value => $label)
                                    <option value="{{ $value }}" @selected((old('_modal') === 'acc_create' ? old('status', 'available') : 'available') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-accommodation-field-error field="status" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Active listing</label>
                            <select name="is_active" class="form-select">
                                <option value="1" @selected(old('_modal') !== 'acc_create' || old('is_active', '1') == '1')>Active</option>
                                <option value="0" @selected(old('_modal') === 'acc_create' && old('is_active') == '0')>Inactive</option>
                            </select>
                            <x-accommodation-field-error field="is_active" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Amenities</label>
                            <div class="dropdown rp-promo-acc-dropdown" data-rp-amenity-wrap>
                                <button class="form-select text-start rp-promo-acc-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
                                    <span data-rp-amenity-label>Select amenities</span>
                                </button>
                                <div class="dropdown-menu p-2" data-rp-amenity-list>
                                    @forelse($amenities as $amenity)
                                        <label class="dropdown-item rp-promo-acc-option">
                                            <input
                                                type="checkbox"
                                                name="amenities[]"
                                                value="{{ $amenity->id }}"
                                                class="form-check-input me-2"
                                                data-rp-amenity-check
                                                data-name="{{ $amenity->name }}"
                                                @checked(old('_modal') === 'acc_create' && collect(old('amenities', []))->contains($amenity->id))
                                            >
                                            <span>{{ $amenity->name }}</span>
                                        </label>
                                    @empty
                                        <div class="dropdown-item text-muted small">No amenities yet.</div>
                                    @endforelse
                                </div>
                            </div>
                            <x-accommodation-field-error field="amenities" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label" for="rpAccCreateCoverImage">Cover image</label>
                            <div class="rp-acc-file-picker" data-rp-acc-file-picker>
                                <span class="rp-acc-file-picker-button" aria-hidden="true">Browse files</span>
                                <span class="rp-acc-file-picker-name" id="rpAccCreateCoverName" data-rp-acc-file-name aria-live="polite">No file selected.</span>
                                <input id="rpAccCreateCoverImage" type="file" name="image" class="rp-acc-file-picker-input" accept=".jpg,.jpeg,.png,.webp" aria-describedby="rpAccCreateCoverName">
                            </div>
                            <x-accommodation-field-error field="image" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label" for="rpAccCreateGalleryImages">Gallery images</label>
                            <div class="rp-acc-file-picker" data-rp-acc-file-picker>
                                <span class="rp-acc-file-picker-button" aria-hidden="true">Browse files</span>
                                <span class="rp-acc-file-picker-name" id="rpAccCreateGalleryName" data-rp-acc-file-name aria-live="polite">No files selected.</span>
                                <input id="rpAccCreateGalleryImages" type="file" name="gallery[]" class="rp-acc-file-picker-input" accept=".jpg,.jpeg,.png,.webp" aria-describedby="rpAccCreateGalleryName" multiple>
                            </div>
                            <x-accommodation-field-error field="gallery" :show="$isAccCreate" />
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. Air-conditioned cabin with a private bathroom.">{{ old('_modal') === 'acc_create' ? old('description') : '' }}</textarea>
                            <x-accommodation-field-error field="description" :show="$isAccCreate" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rp-primary">Create Accommodation</button>
                </div>
        </form>
    </div>
</div>

@foreach($accommodations as $item)
    @php
        $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
        $galleryUrls = collect($item->gallery_urls)->take(5);
        $selectedAmenities = collect(old('_modal') === 'acc_edit_'.$item->id ? old('amenities', []) : $item->amenities->pluck('id')->all());
    @endphp

    {{-- View --}}
    <div class="modal fade" id="rpAccViewModal{{ $item->id }}" tabindex="-1" aria-labelledby="rpAccViewModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content rp-admin-modal rp-acc-view-modal">
                <div class="modal-header">
                    <div class="rp-acc-view-header-title">
                        <span class="rp-acc-view-header-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                        <div>
                            <h2 class="modal-title h5" id="rpAccViewModalLabel{{ $item->id }}">Accommodation details</h2>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="rp-acc-view-card">
                        <div class="rp-acc-view-heading">
                            <div>
                                <div class="rp-acc-view-meta">{{ $item->type->name ?? 'Accommodation' }} · No. {{ $item->number }}</div>
                                <div class="rp-acc-view-title">{{ $item->name }}</div>
                            </div>
                            <div class="rp-acc-view-badges">
                                <span class="rp-acc-view-badge {{ $statusValue === 'available' ? 'is-available' : '' }}">{{ ucfirst(str_replace('_', ' ', $statusValue)) }}</span>
                                <span class="rp-acc-view-badge {{ $item->is_active ? 'is-listed' : 'is-unlisted' }}">{{ $item->is_active ? 'Active listing' : 'Inactive listing' }}</span>
                            </div>
                        </div>

                        <div class="rp-acc-view-layout">
                            <div class="rp-acc-view-gallery">
                                @if($galleryUrls->isEmpty())
                                    <div class="rp-acc-view-empty-photo">
                                        <i class="bi bi-image" aria-hidden="true"></i>
                                        <span>No images uploaded</span>
                                    </div>
                                @else
                                    <div id="rpAccGallery{{ $item->id }}" class="carousel slide rp-acc-carousel" data-bs-touch="true" data-bs-interval="false" aria-label="{{ $item->name }} photo gallery">
                                        <div class="carousel-inner">
                                            @foreach($galleryUrls as $imageUrl)
                                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                                    <button type="button" class="rp-acc-gallery-hit" data-rp-lightbox="{{ $imageUrl }}" aria-label="View {{ $item->name }} photo {{ $loop->iteration }} full size">
                                                        <img src="{{ $imageUrl }}" alt="" class="rp-acc-carousel-backdrop" aria-hidden="true">
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->name }} photo {{ $loop->iteration }}" class="rp-accommodation-gallery-main">
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($galleryUrls->count() > 1)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#rpAccGallery{{ $item->id }}" data-bs-slide="prev" aria-label="Previous photo">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#rpAccGallery{{ $item->id }}" data-bs-slide="next" aria-label="Next photo">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            </button>
                                            <div class="carousel-indicators rp-acc-carousel-indicators">
                                                @foreach($galleryUrls as $imageUrl)
                                                    <button type="button" data-bs-target="#rpAccGallery{{ $item->id }}" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" @if($loop->first) aria-current="true" @endif aria-label="Show photo {{ $loop->iteration }}">
                                                        <img src="{{ $imageUrl }}" alt="">
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="rp-acc-view-details">
                                <div class="rp-acc-view-facts">
                                    <div class="rp-acc-view-fact">
                                        <span class="rp-acc-view-fact-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                                        <div><span>Capacity</span><strong>{{ $item->capacity }} {{ (int) $item->capacity === 1 ? 'guest' : 'guests' }}</strong></div>
                                    </div>
                                    <div class="rp-acc-view-fact">
                                        <span class="rp-acc-view-fact-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                                        <div><span>Rate</span><strong>₱{{ number_format((float) $item->rate, 2) }}</strong></div>
                                    </div>
                                </div>

                                <section class="rp-acc-view-section">
                                    <h3><i class="bi bi-card-text" aria-hidden="true"></i>Description</h3>
                                    <p>{{ $item->description ?: 'No description provided.' }}</p>
                                </section>

                                <section class="rp-acc-view-section">
                                    <h3><i class="bi bi-stars" aria-hidden="true"></i>Amenities</h3>
                                    <div class="rp-acc-view-amenities">
                                        @forelse($item->amenities as $amenity)
                                            <span><i class="bi bi-check2" aria-hidden="true"></i>{{ $amenity->name }}</span>
                                        @empty
                                            <p>No amenities listed.</p>
                                        @endforelse
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit --}}
    @php
        $statusLocked = $item->isStatusLocked();
        $isAccEdit = old('_modal') === 'acc_edit_'.$item->id;
        $selectedStatus = old('_modal') === 'acc_edit_'.$item->id ? old('status', $statusValue) : $statusValue;
        if (! in_array($selectedStatus, \App\Enums\AccommodationStatus::manualValues(), true)) {
            $selectedStatus = \App\Enums\AccommodationStatus::Available->value;
        }
        $activeVal = old('_modal') === 'acc_edit_'.$item->id ? old('is_active') : ($item->is_active ? '1' : '0');
    @endphp
    <div class="modal fade" id="rpAccEditModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form class="modal-content rp-admin-modal" method="POST" action="{{ route('admin.accommodations.update', $item) }}" enctype="multipart/form-data" data-rp-acc-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="acc_edit_{{ $item->id }}">
                    <div class="modal-header">
                        <h2 class="modal-title h5">Edit Accommodation</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rp-promo-form-stack">
                            <div>
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('_modal') === 'acc_edit_'.$item->id ? old('name') : $item->name }}" required>
                                <x-accommodation-field-error field="name" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Number</label>
                                @php $selectedNumber = old('_modal') === 'acc_edit_'.$item->id ? old('number') : $item->number; @endphp
                                <select name="number" class="form-select" required>
                                    <option value="" disabled @selected(! in_array((int) $selectedNumber, range(1, 10), true))>Select a number</option>
                                    @foreach(range(1, 10) as $number)
                                        <option value="{{ $number }}" @selected((string) $selectedNumber === (string) $number)>{{ $number }}</option>
                                    @endforeach
                                </select>
                                <x-accommodation-field-error field="number" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Type</label>
                                <select name="accommodation_type_id" class="form-select" required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" @selected((old('_modal') === 'acc_edit_'.$item->id ? old('accommodation_type_id') : $item->accommodation_type_id) == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <x-accommodation-field-error field="accommodation_type_id" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Capacity</label>
                                <input type="number" min="1" max="50" name="capacity" class="form-control rp-no-spinner" value="{{ old('_modal') === 'acc_edit_'.$item->id ? old('capacity') : $item->capacity }}" required>
                                <x-accommodation-field-error field="capacity" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Rate</label>
                                <input type="number" step="0.01" min="0" max="999999.99" name="rate" class="form-control rp-no-spinner" value="{{ old('_modal') === 'acc_edit_'.$item->id ? old('rate') : $item->rate }}" required>
                                <x-accommodation-field-error field="rate" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Availability</label>
                                @if($statusLocked)
                                    <input type="hidden" name="status" value="{{ $statusValue }}">
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $statusValue)) }}" disabled>
                                    <div class="form-text text-warning">{{ $item->statusLockReason() }}</div>
                                @else
                                    <select name="status" class="form-select" required>
                                        @foreach(\App\Enums\AccommodationStatus::manualLabels() as $value => $label)
                                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-accommodation-field-error field="status" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Active listing</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" @selected($activeVal == '1')>Active</option>
                                    <option value="0" @selected($activeVal == '0')>Inactive</option>
                                </select>
                                <x-accommodation-field-error field="is_active" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label">Amenities</label>
                                <div class="dropdown rp-promo-acc-dropdown" data-rp-amenity-wrap>
                                    <button class="form-select text-start rp-promo-acc-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
                                        <span data-rp-amenity-label>Select amenities</span>
                                    </button>
                                    <div class="dropdown-menu p-2" data-rp-amenity-list>
                                        @foreach($amenities as $amenity)
                                            <label class="dropdown-item rp-promo-acc-option">
                                                <input
                                                    type="checkbox"
                                                    name="amenities[]"
                                                    value="{{ $amenity->id }}"
                                                    class="form-check-input me-2"
                                                    data-rp-amenity-check
                                                    data-name="{{ $amenity->name }}"
                                                    @checked($selectedAmenities->contains($amenity->id))
                                                >
                                                <span>{{ $amenity->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <x-accommodation-field-error field="amenities" :show="$isAccEdit" />
                            </div>
                            <div>
                                <label class="form-label" for="rpAccEditCoverImage{{ $item->id }}">Cover image</label>
                                <div class="rp-acc-file-picker" data-rp-acc-file-picker>
                                    <span class="rp-acc-file-picker-button" aria-hidden="true">Browse files</span>
                                    <span class="rp-acc-file-picker-name" id="rpAccEditCoverName{{ $item->id }}" data-rp-acc-file-name aria-live="polite">No file selected.</span>
                                    <input id="rpAccEditCoverImage{{ $item->id }}" type="file" name="image" class="rp-acc-file-picker-input" accept=".jpg,.jpeg,.png,.webp" aria-describedby="rpAccEditCoverName{{ $item->id }}">
                                </div>
                                <x-accommodation-field-error field="image" :show="$isAccEdit" />
                                @if($item->image)
                                    <img src="{{ $item->image_url }}" alt="" class="img-thumbnail mt-2" style="max-height: 64px;">
                                @endif
                            </div>
                            <div>
                                <label class="form-label" for="rpAccEditGalleryImages{{ $item->id }}">Add gallery images</label>
                                <div class="rp-acc-file-picker" data-rp-acc-file-picker>
                                    <span class="rp-acc-file-picker-button" aria-hidden="true">Browse files</span>
                                    <span class="rp-acc-file-picker-name" id="rpAccEditGalleryName{{ $item->id }}" data-rp-acc-file-name aria-live="polite">No files selected.</span>
                                    <input id="rpAccEditGalleryImages{{ $item->id }}" type="file" name="gallery[]" class="rp-acc-file-picker-input" accept=".jpg,.jpeg,.png,.webp" aria-describedby="rpAccEditGalleryName{{ $item->id }}" multiple>
                                </div>
                                <x-accommodation-field-error field="gallery" :show="$isAccEdit" />
                            </div>
                            @if($item->gallery_items)
                                <div>
                                    <label class="form-label">Current gallery</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($item->gallery_items as $index => $galleryItem)
                                            <div class="rp-gallery-thumb-check">
                                                <img src="{{ $galleryItem['url'] }}" alt="" class="img-fluid rounded border">
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="{{ $galleryItem['path'] }}" id="removeGallery{{ $item->id }}_{{ $index }}">
                                                    <label class="form-check-label small" for="removeGallery{{ $item->id }}_{{ $index }}">Remove</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <x-accommodation-field-error field="remove_gallery" :show="$isAccEdit" />
                                </div>
                            @endif
                            <div>
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('_modal') === 'acc_edit_'.$item->id ? old('description') : $item->description }}</textarea>
                                <x-accommodation-field-error field="description" :show="$isAccEdit" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rp-primary">Save Changes</button>
                    </div>
            </form>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalKey = @json(old('_modal') ?: session('open_acc_view'));
    let targetId = null;
    if (modalKey === 'acc_create') targetId = 'rpAccCreateModal';
    else if (typeof modalKey === 'string' && modalKey.startsWith('acc_edit_')) targetId = `rpAccEditModal${modalKey.replace('acc_edit_', '')}`;
    else if (typeof modalKey === 'string' && modalKey.startsWith('acc_view_')) targetId = `rpAccViewModal${modalKey.replace('acc_view_', '')}`;
    const el = targetId ? document.getElementById(targetId) : null;
    if (el && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(el).show();

    document.querySelectorAll('[data-rp-acc-form]').forEach((form) => {
        const list = form.querySelector('[data-rp-amenity-list]');
        const labelEl = form.querySelector('[data-rp-amenity-label]');
        const syncLabel = () => {
            if (!labelEl) return;
            const selected = Array.from(list?.querySelectorAll('[data-rp-amenity-check]:checked') || []);
            if (!selected.length) {
                labelEl.textContent = 'Select amenities';
                return;
            }
            if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.name || '1 selected';
                return;
            }
            labelEl.textContent = `${selected.length} amenities selected`;
        };
        list?.addEventListener('change', syncLabel);
        syncLabel();
    });

    document.querySelectorAll('[data-rp-acc-file-picker]').forEach((picker) => {
        const input = picker.querySelector('input[type="file"]');
        const name = picker.querySelector('[data-rp-acc-file-name]');
        input?.addEventListener('change', () => {
            const files = Array.from(input.files || []);
            name.textContent = files.length === 0
                ? (input.multiple ? 'No files selected.' : 'No file selected.')
                : files.length === 1 ? files[0].name : `${files.length} files selected`;
        });
    });

    document.querySelectorAll('[data-rp-stop-row-click]').forEach((cell) => {
        cell.addEventListener('click', (event) => event.stopPropagation());
        cell.addEventListener('keydown', (event) => event.stopPropagation());
    });

    document.querySelectorAll('.rp-clickable-row').forEach((row) => {
        const openDetails = () => {
            const selector = row.dataset.rpRowModal;
            if (!selector) return;
            const target = document.querySelector(selector);
            if (target && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(target).show();
            }
        };

        row.addEventListener('click', (event) => {
            if (event.target.closest('button, a, form, input, select, textarea, label, [data-rp-stop-row-click]')) return;
            openDetails();
        });

        row.addEventListener('keydown', (event) => {
            if (event.target !== row) return;
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDetails();
            }
        });
    });

    let lightbox = document.getElementById('rpAccLightbox');
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.id = 'rpAccLightbox';
        lightbox.className = 'rp-acc-lightbox';
        lightbox.hidden = true;
        lightbox.innerHTML = '<button type="button" class="rp-acc-lightbox-close" aria-label="Close">&times;</button><img alt="Accommodation photo">';
        document.body.appendChild(lightbox);
        lightbox.addEventListener('click', () => { lightbox.hidden = true; });
        lightbox.querySelector('.rp-acc-lightbox-close')?.addEventListener('click', (event) => {
            event.stopPropagation();
            lightbox.hidden = true;
        });
    }

    document.querySelectorAll('[data-rp-lightbox]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const img = lightbox.querySelector('img');
            if (img) img.src = btn.dataset.rpLightbox;
            lightbox.hidden = false;
        });
    });
});
</script>
@endpush
