@extends('layouts.dashboard')

@section('title', 'Settings')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'System Settings')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@php
    $value = function (string $key, $default = '') use ($settings) {
        if (is_array($settings)) {
            return old($key, $settings[$key] ?? $default);
        }
        return old($key, data_get($settings, $key, $default));
    };
@endphp
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="rp-flow-card mb-4">
        <h3 class="h6 mb-3">Resort Details</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Resort name</label>
                <input type="text" name="resort_name" class="form-control" value="{{ $value('resort_name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Resort subtitle</label>
                <input type="text" name="resort_subtitle" class="form-control" value="{{ $value('resort_subtitle', 'Bluepool Waterpark') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <input type="text" name="resort_address" class="form-control" value="{{ $value('resort_address', 'Philippines') }}" placeholder="City / Province, Philippines">
            </div>
            <div class="col-md-4">
                <label class="form-label">Resort email</label>
                <input type="email" name="resort_email" class="form-control" value="{{ $value('resort_email') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mobile phone</label>
                <input type="text" name="resort_phone" class="form-control" value="{{ $value('resort_phone') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Landline</label>
                <input type="text" name="resort_phone_landline" class="form-control" value="{{ $value('resort_phone_landline') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <input type="text" name="currency" class="form-control" value="{{ $value('currency', 'PHP') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Check-in time</label>
                <input type="time" name="check_in_time" class="form-control" value="{{ $value('check_in_time', '14:00') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Check-out time</label>
                <input type="time" name="check_out_time" class="form-control" value="{{ $value('check_out_time', '12:00') }}" required>
            </div>
        </div>
    </div>

    <div class="rp-flow-card mb-4">
        <h3 class="h6 mb-3">Payment Details <span class="text-muted fw-normal">(shown to guests)</span></h3>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">GCash number</label>
                <input type="text" name="gcash_number" class="form-control" value="{{ $value('gcash_number', '09505584607') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">GCash account name</label>
                <input type="text" name="gcash_name" class="form-control" value="{{ $value('gcash_name') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bank name</label>
                <input type="text" name="bank_name" class="form-control" value="{{ $value('bank_name', 'BDO') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bank account name</label>
                <input type="text" name="bank_account_name" class="form-control" value="{{ $value('bank_account_name') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bank account number</label>
                <input type="text" name="bank_account_number" class="form-control" value="{{ $value('bank_account_number') }}">
            </div>
        </div>

        <button class="rp-avail-btn-primary rp-avail-btn-primary--inline mt-3">Save Settings</button>
    </div>
</form>
@endsection
