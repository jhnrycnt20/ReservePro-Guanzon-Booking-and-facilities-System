@php
    $settingsBag = $resortSettings ?? [];
    $settingValue = function (string $key, $default = '') use ($settingsBag) {
        return old($key, $settingsBag[$key] ?? $default);
    };
@endphp
<div class="modal fade" id="rpSettingsModal" tabindex="-1" aria-labelledby="rpSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rp-admin-modal">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="settings">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="rpSettingsModalLabel">System Settings</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="h6 mb-3">Resort Details</h3>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Resort name</label>
                            <input type="text" name="resort_name" class="form-control" value="{{ $settingValue('resort_name', 'Guanzon Beach') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Resort subtitle</label>
                            <input type="text" name="resort_subtitle" class="form-control" value="{{ $settingValue('resort_subtitle', 'Bluepool Waterpark') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="resort_address" class="form-control" value="{{ $settingValue('resort_address', 'Guanzon Beach Resort, Purok Lawis, Brgy. Langtad, City of Naga, Cebu') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Resort email</label>
                            <input type="email" name="resort_email" class="form-control" value="{{ $settingValue('resort_email', 'info@guanzonresort.com') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile phone</label>
                            <input type="text" name="resort_phone" class="form-control" value="{{ $settingValue('resort_phone', '09190644054') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Landline</label>
                            <input type="text" name="resort_phone_landline" class="form-control" value="{{ $settingValue('resort_phone_landline', '265-7942') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" value="{{ $settingValue('currency', 'PHP') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check-in time</label>
                            <input type="time" name="check_in_time" class="form-control" value="{{ $settingValue('check_in_time', '14:00') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check-out time</label>
                            <input type="time" name="check_out_time" class="form-control" value="{{ $settingValue('check_out_time', '12:00') }}" required>
                        </div>
                    </div>

                    <h3 class="h6 mb-3">Payment Details <span class="text-muted fw-normal">(shown to guests)</span></h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">GCash number</label>
                            <input type="text" name="gcash_number" class="form-control" value="{{ $settingValue('gcash_number', '09505584607') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GCash account name</label>
                            <input type="text" name="gcash_name" class="form-control" value="{{ $settingValue('gcash_name', 'Guanzon Beach') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ $settingValue('bank_name', 'BDO') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank account name</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ $settingValue('bank_account_name', 'Guanzon Beach') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank account number</label>
                            <input type="text" name="bank_account_number" class="form-control" value="{{ $settingValue('bank_account_number', '0000-0000-0000') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rp-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
