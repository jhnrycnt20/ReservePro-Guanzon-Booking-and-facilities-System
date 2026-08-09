@extends('layouts.dashboard')

@section('title', 'Settings')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'System Settings')
@section('page_subtitle', 'Resort profile and default times')
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
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="rp-card">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Resort name</label>
                        <input type="text" name="resort_name" class="form-control" value="{{ $value('resort_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resort email</label>
                        <input type="email" name="resort_email" class="form-control" value="{{ $value('resort_email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resort phone</label>
                        <input type="text" name="resort_phone" class="form-control" value="{{ $value('resort_phone') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control" value="{{ $value('currency', 'PHP') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-in time</label>
                        <input type="time" name="check_in_time" class="form-control" value="{{ $value('check_in_time', '14:00') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Check-out time</label>
                        <input type="time" name="check_out_time" class="form-control" value="{{ $value('check_out_time', '12:00') }}" required>
                    </div>
                </div>
                <button class="btn btn-rp-primary mt-4">Save Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
