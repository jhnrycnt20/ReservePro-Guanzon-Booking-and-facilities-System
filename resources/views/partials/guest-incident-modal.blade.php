<div class="modal fade rp-incident-report-modal" id="rpGuestIncidentModal" tabindex="-1" aria-labelledby="rpGuestIncidentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="rpGuestIncidentModalTitle">Report Incident</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('guest.incidents.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_modal" value="incident">

                <div class="modal-body rp-incident-create-card">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="rpIncidentBooking">Related booking</label>
                            <select id="rpIncidentBooking" name="booking_id" class="form-select @error('booking_id') is-invalid @enderror" data-rp-incident-booking>
                                <option value="">No related booking</option>
                                @foreach($incidentReportBookings as $booking)
                                    <option value="{{ $booking->id }}" @selected((string) old('booking_id', request('booking_id')) === (string) $booking->id)>
                                        {{ $booking->short_number ?? $booking->booking_number }} — {{ $booking->accommodation->name ?? 'Reservation' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="rpIncidentType">Report type</label>
                            <select id="rpIncidentType" name="report_type" class="form-select @error('report_type') is-invalid @enderror" required>
                                @foreach(['incident' => 'Incident', 'broken_amenity' => 'Broken Amenity', 'complaint' => 'Complaint', 'maintenance' => 'Maintenance'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('report_type', 'incident') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('report_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="rpIncidentDescription">Description</label>
                            <textarea id="rpIncidentDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="5000" required placeholder="Describe what happened…">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="rpIncidentPhoto">Photo <span class="text-muted fw-normal">(optional)</span></label>
                            <input id="rpIncidentPhoto" type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, or WEBP. Max 5MB.</div>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rp-btn-check-availability">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('rpGuestIncidentModal');
    const bookingSelect = modal?.querySelector('[data-rp-incident-booking]');
    if (!modal || !bookingSelect) return;

    modal.addEventListener('show.bs.modal', (event) => {
        const bookingId = event.relatedTarget?.dataset.rpIncidentBooking;
        if (bookingId && bookingSelect.querySelector(`option[value="${CSS.escape(bookingId)}"]`)) {
            bookingSelect.value = bookingId;
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        const url = new URL(window.location.href);
        if (!url.searchParams.has('new')) return;
        url.searchParams.delete('new');
        url.searchParams.delete('booking_id');
        window.history.replaceState(window.history.state, '', url);
    });
});
</script>
@endpush
